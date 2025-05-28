<?php

namespace App\Controller;

use App\Entity\TrainingExercises;
use App\Enum\MuscleGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/exercise-library')]
#[IsGranted('ROLE_USER')]
class ExerciseLibraryController extends AbstractController
{
    #[Route('/', name: 'app_exercises_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $muscleGroup = $request->query->get('muscle_group');
        $search = $request->query->get('search');

        $queryBuilder = $entityManager->getRepository(TrainingExercises::class)
            ->createQueryBuilder('e');

        if ($muscleGroup && $muscleGroup !== 'all') {
            $queryBuilder->andWhere('e.target_muscle_group = :muscleGroup')
                ->setParameter('muscleGroup', $muscleGroup);
        }

        if ($search) {
            $queryBuilder->andWhere('e.name LIKE :search OR e.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $exercises = $queryBuilder->orderBy('e.name', 'ASC')->getQuery()->getResult();

        // Get muscle groups
        $muscleGroups = MuscleGroup::cases();

        return $this->render('user_dashboard/exercise_library/index.html.twig', [
            'exercises' => $exercises,
            'muscleGroups' => $muscleGroups,
            'currentMuscleGroup' => $muscleGroup,
            'currentSearch' => $search
        ]);
    }

    #[Route('/api/exercises', name: 'app_exercises_api', methods: ['GET'])]
    public function getExercisesApi(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $page = max(1, (int) $request->query->get('page', 1));
            $limit = min(50, max(1, (int) $request->query->get('limit', 12)));
            $muscleGroup = $request->query->get('muscle_group');
            $search = $request->query->get('search');

            $queryBuilder = $entityManager->getRepository(TrainingExercises::class)
                ->createQueryBuilder('e');

            if ($muscleGroup && $muscleGroup !== 'all') {
                $queryBuilder->andWhere('e.target_muscle_group = :muscleGroup')
                    ->setParameter('muscleGroup', $muscleGroup);
            }

            if ($search) {
                $queryBuilder->andWhere('e.name LIKE :search OR e.description LIKE :search')
                    ->setParameter('search', '%' . $search . '%');
            }

            $totalQuery = clone $queryBuilder;
            $total = $totalQuery->select('COUNT(e.id)')->getQuery()->getSingleScalarResult();

            $exercises = $queryBuilder
                ->orderBy('e.name', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $exerciseData = array_map(function ($exercise) {
                return [
                    'id' => $exercise->getId(),
                    'name' => $exercise->getName(),
                    'description' => $exercise->getDescription(),
                    'muscleGroup' => $exercise->getTargetMuscleGroup() ? $exercise->getTargetMuscleGroup()->value : null,
                    'muscleGroupLabel' => $exercise->getTargetMuscleGroup() ? $exercise->getTargetMuscleGroup()->getLabel() : 'Unknown',
                    'imageUrl' => $exercise->getImageUrl(),
                    'videoUrl' => $exercise->getVideoUrl()
                ];
            }, $exercises);

            return $this->json([
                'success' => true,
                'exercises' => $exerciseData,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error loading exercises: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/exercise/{id}', name: 'app_exercises_show', methods: ['GET'])]
    public function exerciseDetail(int $id, EntityManagerInterface $entityManager): Response
    {
        $exercise = $entityManager->getRepository(TrainingExercises::class)->find($id);

        if (!$exercise) {
            throw $this->createNotFoundException('Exercise not found.');
        }

        // Get user's recent performance with this exercise
        $user = $this->getUser();
        $recentPerformance = $entityManager->createQueryBuilder()
            ->select('wld.weight, wld.reps, wld.sets, wl.created_at')
            ->from('App\Entity\WorkoutLogDetails', 'wld')
            ->join('wld.workoutLog', 'wl')
            ->where('wl.user = :user')
            ->andWhere('wld.exercise = :exercise')
            ->andWhere('wl.is_completed = true')
            ->setParameter('user', $user)
            ->setParameter('exercise', $exercise)
            ->orderBy('wl.created_at', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Get similar exercises (same muscle group)
        $similarExercises = $entityManager->getRepository(TrainingExercises::class)
            ->createQueryBuilder('e')
            ->where('e.target_muscle_group = :muscleGroup')
            ->andWhere('e.id != :currentId')
            ->setParameter('muscleGroup', $exercise->getTargetMuscleGroup())
            ->setParameter('currentId', $exercise->getId())
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();

        return $this->render('user_dashboard/exercise_library/detail.html.twig', [
            'exercise' => $exercise,
            'recentPerformance' => $recentPerformance,
            'similarExercises' => $similarExercises
        ]);
    }

    #[Route('/muscle-groups', name: 'app_exercises_muscle_groups_api', methods: ['GET'])]
    public function getMuscleGroups(): JsonResponse
    {
        $muscleGroups = array_map(function ($muscleGroup) {
            return [
                'value' => $muscleGroup->value,
                'label' => $muscleGroup->getLabel()
            ];
        }, MuscleGroup::cases());

        return $this->json($muscleGroups);
    }

    #[Route('/create-custom', name: 'app_exercises_create', methods: ['POST'])]
    public function createCustomExercise(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Debug information
            error_log('Exercise creation request received: ' . json_encode($data));

            // Validation
            if (empty($data['name']) || empty($data['description']) || empty($data['muscleGroup'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'All fields must be filled'
                ], 400);
            }

            // Check if muscle group is valid
            $muscleGroup = null;
            foreach (MuscleGroup::cases() as $mg) {
                if ($mg->value === $data['muscleGroup']) {
                    $muscleGroup = $mg;
                    break;
                }
            }

            if (!$muscleGroup) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid muscle group'
                ], 400);
            }

            // Check if exercise with same name already exists
            $existingExercise = $entityManager->getRepository(TrainingExercises::class)
                ->findOneBy(['name' => $data['name']]);

            if ($existingExercise) {
                return $this->json([
                    'success' => false,
                    'message' => 'An exercise with this name already exists'
                ], 400);
            }

            // Create new exercise
            $exercise = new TrainingExercises();
            $exercise->setName($data['name']);
            $exercise->setDescription($data['description']);
            $exercise->setTargetMuscleGroup($muscleGroup);

            // Add optional fields
            if (!empty($data['imageUrl'])) {
                $exercise->setImageUrl($data['imageUrl']);
            }
            if (!empty($data['videoUrl'])) {
                $exercise->setVideoUrl($data['videoUrl']);
            }
            if (!empty($data['instructions'])) {
                $exercise->setInstructions($data['instructions']);
            }

            $entityManager->persist($exercise);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Custom exercise created successfully',
                'exercise' => [
                    'id' => $exercise->getId(),
                    'name' => $exercise->getName(),
                    'description' => $exercise->getDescription(),
                    'muscleGroup' => $exercise->getTargetMuscleGroup()->value
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while creating the exercise: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/delete/{id}', name: 'app_exercises_delete', methods: ['DELETE'])]
    public function deleteExercise(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $exercise = $entityManager->getRepository(TrainingExercises::class)->find($id);

            if (!$exercise) {
                return $this->json([
                    'success' => false,
                    'message' => 'Exercise not found'
                ], 404);
            }

            // Check if exercise is used in programs
            $programCount = $entityManager->createQueryBuilder()
                ->select('COUNT(tp.id)')
                ->from('App\Entity\TrainingProgram', 'tp')
                ->join('tp.training_exercises', 'te')
                ->where('te.id = :exerciseId')
                ->setParameter('exerciseId', $id)
                ->getQuery()
                ->getSingleScalarResult();

            if ($programCount > 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'This exercise is used in ' . $programCount . ' programs. Please remove it from programs first.'
                ], 400);
            }

            // Check if exercise is used in workout logs
            $workoutLogCount = $entityManager->createQueryBuilder()
                ->select('COUNT(wld.id)')
                ->from('App\Entity\WorkoutLogDetails', 'wld')
                ->where('wld.exercise = :exerciseId')
                ->setParameter('exerciseId', $id)
                ->getQuery()
                ->getSingleScalarResult();

            if ($workoutLogCount > 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'This exercise is used in workout logs. Cannot be deleted.'
                ], 400);
            }

            // Delete exercise
            $entityManager->remove($exercise);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Exercise deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while deleting the exercise: ' . $e->getMessage()
            ], 500);
        }
    }
}
