<?php

namespace App\Controller;

use App\Entity\TrainingExercises;
use App\Enum\MuscleGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/create', name: 'app_exercises_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $name = trim($request->request->get('name'));
            $description = trim($request->request->get('description'));
            $muscleGroupValue = $request->request->get('muscle_group');
            $imageUrl = trim($request->request->get('image_url'));
            $videoUrl = trim($request->request->get('video_url'));

            // Validation
            if (empty($name) || empty($description) || empty($muscleGroupValue)) {
                $this->addFlash('error', 'Name, description and muscle group are required.');
                return $this->redirectToRoute('app_exercises_create');
            }

            // Check if exercise with same name already exists
            $existingExercise = $entityManager->getRepository(TrainingExercises::class)
                ->findOneBy(['name' => $name]);

            if ($existingExercise) {
                $this->addFlash('error', 'An exercise with this name already exists.');
                return $this->redirectToRoute('app_exercises_create');
            }

            // Find muscle group enum
            $muscleGroup = null;
            foreach (MuscleGroup::cases() as $mg) {
                if ($mg->value === $muscleGroupValue) {
                    $muscleGroup = $mg;
                    break;
                }
            }

            if (!$muscleGroup) {
                $this->addFlash('error', 'Invalid muscle group selected.');
                return $this->redirectToRoute('app_exercises_create');
            }

            // Create new exercise
            $exercise = new TrainingExercises();
            $exercise->setName($name);
            $exercise->setDescription($description);
            $exercise->setTargetMuscleGroup($muscleGroup);

            // Add optional image and video URLs
            if (!empty($imageUrl)) {
                $exercise->setImageUrl($imageUrl);
            }
            if (!empty($videoUrl)) {
                $exercise->setVideoUrl($videoUrl);
            }

            $entityManager->persist($exercise);
            $entityManager->flush();

            $this->addFlash('success', 'Exercise created successfully!');
            return $this->redirectToRoute('app_exercises_index');
        }

        return $this->render('user_dashboard/exercise_library/create.html.twig', [
            'muscleGroups' => MuscleGroup::cases()
        ]);
    }

    #[Route('/{id}', name: 'app_exercises_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $exercise = $entityManager->getRepository(TrainingExercises::class)->find($id);

        if (!$exercise) {
            throw $this->createNotFoundException('Exercise not found.');
        }

        return $this->render('user_dashboard/exercise_library/show.html.twig', [
            'exercise' => $exercise
        ]);
    }

    #[Route('/{id}/delete', name: 'app_exercises_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $exercise = $entityManager->getRepository(TrainingExercises::class)->find($id);

        if (!$exercise) {
            $this->addFlash('error', 'Exercise not found.');
            return $this->redirectToRoute('app_exercises_index');
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
            $this->addFlash('error', 'This exercise is used in programs and cannot be deleted.');
            return $this->redirectToRoute('app_exercises_index');
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
            $this->addFlash('error', 'This exercise is used in workout logs and cannot be deleted.');
            return $this->redirectToRoute('app_exercises_index');
        }

        $entityManager->remove($exercise);
        $entityManager->flush();

        $this->addFlash('success', 'Exercise deleted successfully!');
        return $this->redirectToRoute('app_exercises_index');
    }
}
