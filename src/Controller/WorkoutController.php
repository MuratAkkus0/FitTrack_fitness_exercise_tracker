<?php

namespace App\Controller;

use App\Entity\WorkoutLogs;
use App\Entity\WorkoutLogDetails;
use App\Entity\TrainingProgram;
use App\Entity\TrainingExercises;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/workout')]
#[IsGranted('ROLE_USER')]
class WorkoutController extends AbstractController
{
    #[Route('/start/{programId}', name: 'app_workout_start', methods: ['GET'])]
    public function startWorkout(int $programId, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $program = $entityManager->getRepository(TrainingProgram::class)->find($programId);

        if (!$program || $program->getUsers() !== $user) {
            throw $this->createNotFoundException('Program not found or you do not have access.');
        }

        return $this->render('user_dashboard/workout/start.html.twig', [
            'program' => $program,
            'exercises' => $program->getTrainingExercises()
        ]);
    }

    #[Route('/save', name: 'app_workout_save', methods: ['POST'])]
    public function saveWorkout(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->getUser();

            // Validation
            if (empty($data['programId'])) {
                return $this->json(['success' => false, 'message' => 'Program ID is required'], 400);
            }

            $program = $entityManager->getRepository(TrainingProgram::class)->find($data['programId']);
            if (!$program || $program->getUsers() !== $user) {
                return $this->json(['success' => false, 'message' => 'Program not found'], 404);
            }

            // Create Workout Log
            $workoutLog = new WorkoutLogs();
            $workoutLog->setUser($user);
            $workoutLog->setTrainingProgram($program);
            $workoutLog->setCreatedAt(new \DateTimeImmutable());
            $workoutLog->setDuration($data['duration'] ?? '0.00');
            $workoutLog->setIsCompleted($data['isCompleted'] ?? false);
            $workoutLog->setNotes($data['notes'] ?? null);
            $workoutLog->setEstimatedCalories($data['estimatedCalories'] ?? null);
            $workoutLog->setTotalVolume($data['totalVolume'] ?? null);
            $workoutLog->setTotalReps($data['totalReps'] ?? null);

            $entityManager->persist($workoutLog);

            // Save exercise details
            if (isset($data['exercises']) && is_array($data['exercises'])) {
                foreach ($data['exercises'] as $exerciseData) {
                    $exercise = $entityManager->getRepository(TrainingExercises::class)->find($exerciseData['exerciseId']);
                    if ($exercise) {
                        $workoutDetail = new WorkoutLogDetails();
                        $workoutDetail->setWorkoutLog($workoutLog);
                        $workoutDetail->setExercise($exercise);
                        $workoutDetail->setSets($exerciseData['sets'] ?? 0);
                        $workoutDetail->setReps($exerciseData['reps'] ?? 0);
                        $workoutDetail->setWeight($exerciseData['weight'] ?? '0.00');
                        $workoutDetail->setNotes($exerciseData['notes'] ?? null);

                        $entityManager->persist($workoutDetail);
                    }
                }
            }

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Workout saved successfully',
                'workoutId' => $workoutLog->getId()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while saving the workout: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/history', name: 'app_workout_history', methods: ['GET'])]
    public function workoutHistory(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        $limit = 20;
        $offset = (int) $request->query->get('page', 1) - 1;
        $offset = max(0, $offset) * $limit;

        // Get workouts with pagination
        $workouts = $entityManager->getRepository(WorkoutLogs::class)
            ->createQueryBuilder('w')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->orderBy('w.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();

        // Get total workout count for pagination
        $totalWorkouts = $entityManager->getRepository(WorkoutLogs::class)->count(['user' => $user]);

        // Get statistics
        $completedWorkouts = $entityManager->getRepository(WorkoutLogs::class)->count([
            'user' => $user,
            'is_completed' => true
        ]);

        // This month's workouts
        $monthStart = new \DateTime('first day of this month');
        $monthEnd = new \DateTime('last day of this month 23:59:59');

        $thisMonthWorkouts = $entityManager->getRepository(WorkoutLogs::class)
            ->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.user = :user')
            ->andWhere('w.created_at >= :monthStart')
            ->andWhere('w.created_at <= :monthEnd')
            ->setParameter('user', $user)
            ->setParameter('monthStart', $monthStart)
            ->setParameter('monthEnd', $monthEnd)
            ->getQuery()
            ->getSingleScalarResult();

        // Format workouts for template
        $formattedWorkouts = [];
        foreach ($workouts as $workout) {
            $formattedWorkouts[] = [
                'id' => $workout->getId(),
                'programName' => $workout->getTrainingProgram()?->getName(),
                'date' => $workout->getCreatedAt()?->format('M j, Y'),
                'time' => $workout->getCreatedAt()?->format('H:i'),
                'duration' => $workout->getDuration(),
                'isCompleted' => $workout->isCompleted(),
                'notes' => $workout->getNotes(),
                'exerciseCount' => count($workout->getWorkoutLogDetails()),
                'estimatedCalories' => $workout->getEstimatedCalories(),
                'totalVolume' => $workout->getTotalVolume(),
                'totalReps' => $workout->getTotalReps(),
                'createdAt' => $workout->getCreatedAt()
            ];
        }

        return $this->render('user_dashboard/workout/history.html.twig', [
            'workouts' => $formattedWorkouts,
            'stats' => [
                'totalWorkouts' => $totalWorkouts,
                'completedWorkouts' => $completedWorkouts,
                'thisMonthWorkouts' => $thisMonthWorkouts,
                'completionRate' => $totalWorkouts > 0 ? round(($completedWorkouts / $totalWorkouts) * 100) : 0
            ],
            'pagination' => [
                'hasMore' => $totalWorkouts > ($offset + $limit),
                'currentPage' => ($offset / $limit) + 1,
                'totalPages' => ceil($totalWorkouts / $limit)
            ]
        ]);
    }

    #[Route('/details/{id}', name: 'app_workout_details', methods: ['GET'])]
    public function workoutDetails(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $workout = $entityManager->getRepository(WorkoutLogs::class)->find($id);

        if (!$workout || $workout->getUser() !== $user) {
            throw $this->createNotFoundException('Workout not found or you do not have access.');
        }

        $exerciseDetails = [];
        foreach ($workout->getWorkoutLogDetails() as $detail) {
            $exerciseDetails[] = [
                'exerciseName' => $detail->getExercise()?->getName(),
                'sets' => $detail->getSets(),
                'reps' => $detail->getReps(),
                'weight' => $detail->getWeight(),
                'notes' => $detail->getNotes()
            ];
        }

        return $this->render('user_dashboard/workout/details.html.twig', [
            'workout' => $workout,
            'exerciseDetails' => $exerciseDetails
        ]);
    }

    #[Route('/today', name: 'app_workout_today', methods: ['GET'])]
    public function todaysWorkout(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        // Get today's completed workouts
        $todaysWorkouts = $entityManager->getRepository(WorkoutLogs::class)
            ->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.created_at >= :today')
            ->andWhere('w.created_at < :tomorrow')
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->orderBy('w.created_at', 'DESC')
            ->getQuery()
            ->getResult();

        // Get user's active programs for quick start
        $activePrograms = $entityManager->getRepository(TrainingProgram::class)->findBy(
            ['users' => $user, 'is_active' => true],
            ['created_at' => 'DESC']
        );

        // Get this week's workout count
        $weekStart = new \DateTime('monday this week');
        $weekEnd = new \DateTime('sunday this week 23:59:59');

        $weeklyWorkoutCount = $entityManager->getRepository(WorkoutLogs::class)
            ->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.user = :user')
            ->andWhere('w.created_at >= :weekStart')
            ->andWhere('w.created_at <= :weekEnd')
            ->setParameter('user', $user)
            ->setParameter('weekStart', $weekStart)
            ->setParameter('weekEnd', $weekEnd)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('user_dashboard/todays_workout/index.html.twig', [
            'todaysWorkouts' => $todaysWorkouts,
            'activePrograms' => $activePrograms,
            'weeklyWorkoutCount' => $weeklyWorkoutCount,
            'currentDate' => $today->format('F j, Y'),
            'currentDay' => $today->format('l')
        ]);
    }
}
