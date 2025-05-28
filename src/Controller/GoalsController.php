<?php

namespace App\Controller;

use App\Entity\FitnessGoal;
use App\Repository\FitnessGoalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/goals')]
#[IsGranted('ROLE_USER')]
class GoalsController extends AbstractController
{
    #[Route('/', name: 'app_goals_index', methods: ['GET'])]
    public function index(FitnessGoalRepository $goalRepository): Response
    {
        $user = $this->getUser();

        $activeGoals = $goalRepository->findActiveGoalsByUser($user);
        $completedGoals = $goalRepository->findCompletedGoalsByUser($user);

        return $this->render('user_dashboard/goals/index.html.twig', [
            'activeGoals' => $activeGoals,
            'completedGoals' => $completedGoals
        ]);
    }

    #[Route('/create', name: 'app_goals_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->getUser();

            // Validation
            if (empty($data['title']) || empty($data['goalType'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Title and goal type are required'
                ], 400);
            }

            $goal = new FitnessGoal();
            $goal->setUser($user);
            $goal->setTitle($data['title']);
            $goal->setDescription($data['description'] ?? null);
            $goal->setGoalType($data['goalType']);
            $goal->setTargetValue($data['targetValue'] ?? null);
            $goal->setUnit($data['unit'] ?? null);

            if (isset($data['targetDate'])) {
                $goal->setTargetDate(new \DateTimeImmutable($data['targetDate']));
            }

            $entityManager->persist($goal);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Goal created successfully',
                'goal' => [
                    'id' => $goal->getId(),
                    'title' => $goal->getTitle(),
                    'goalType' => $goal->getGoalType(),
                    'targetValue' => $goal->getTargetValue(),
                    'currentValue' => $goal->getCurrentValue(),
                    'progressPercentage' => $goal->getProgressPercentage()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while creating the goal: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/update-progress', name: 'app_goals_update_progress', methods: ['POST'])]
    public function updateProgress(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->getUser();

            $goal = $entityManager->getRepository(FitnessGoal::class)->find($id);

            if (!$goal || $goal->getUser() !== $user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Goal not found or you do not have access'
                ], 404);
            }

            if (isset($data['currentValue'])) {
                $goal->setCurrentValue($data['currentValue']);

                // Check if goal is completed
                if ($goal->getTargetValue() && $goal->getCurrentValue() >= $goal->getTargetValue()) {
                    $goal->setIsCompleted(true);
                }
            }

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'goal' => [
                    'id' => $goal->getId(),
                    'currentValue' => $goal->getCurrentValue(),
                    'progressPercentage' => $goal->getProgressPercentage(),
                    'isCompleted' => $goal->isCompleted()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while updating progress: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/complete', name: 'app_goals_complete', methods: ['POST'])]
    public function completeGoal(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->getUser();
            $goal = $entityManager->getRepository(FitnessGoal::class)->find($id);

            if (!$goal || $goal->getUser() !== $user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Goal not found or you do not have access'
                ], 404);
            }

            $goal->setIsCompleted(true);
            $goal->setIsActive(false);

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Goal marked as completed'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while completing the goal: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/delete', name: 'app_goals_delete', methods: ['DELETE'])]
    public function deleteGoal(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->getUser();
            $goal = $entityManager->getRepository(FitnessGoal::class)->find($id);

            if (!$goal || $goal->getUser() !== $user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Goal not found or you do not have access'
                ], 404);
            }

            $entityManager->remove($goal);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Goal deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while deleting the goal: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/types', name: 'app_goals_types', methods: ['GET'])]
    public function getGoalTypes(): JsonResponse
    {
        $goalTypes = [
            'weight_loss' => 'Weight Loss',
            'muscle_gain' => 'Muscle Gain',
            'strength' => 'Strength Increase',
            'endurance' => 'Endurance',
            'flexibility' => 'Flexibility',
            'body_fat' => 'Body Fat Percentage',
            'workout_frequency' => 'Workout Frequency',
            'personal_record' => 'Personal Record',
            'distance' => 'Distance',
            'time' => 'Time'
        ];

        return $this->json($goalTypes);
    }

    #[Route('/dashboard-stats', name: 'app_goals_dashboard_stats', methods: ['GET'])]
    public function getDashboardStats(FitnessGoalRepository $goalRepository): JsonResponse
    {
        $user = $this->getUser();

        $activeGoals = $goalRepository->findActiveGoalsByUser($user);
        $completedGoals = $goalRepository->findCompletedGoalsByUser($user);

        $totalGoals = count($activeGoals) + count($completedGoals);
        $completionRate = $totalGoals > 0 ? (count($completedGoals) / $totalGoals) * 100 : 0;

        // Upcoming goals (those with end date within 1 week)
        $upcomingGoals = array_filter($activeGoals, function ($goal) {
            $remainingDays = $goal->getRemainingDays();
            return $remainingDays !== null && $remainingDays <= 7 && $remainingDays >= 0;
        });

        return $this->json([
            'totalActiveGoals' => count($activeGoals),
            'totalCompletedGoals' => count($completedGoals),
            'completionRate' => round($completionRate, 1),
            'upcomingGoals' => count($upcomingGoals),
            'recentGoals' => array_slice($activeGoals, 0, 3)
        ]);
    }
}
