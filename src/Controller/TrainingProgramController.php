<?php

namespace App\Controller;

use App\Entity\TrainingProgram;
use App\Entity\TrainingExercises;
use App\Form\TrainingProgramFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/my-programs')]
#[IsGranted('ROLE_USER')]
class TrainingProgramController extends AbstractController
{
    #[Route('/', name: 'app_programs_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->render('user_dashboard/my_programs/index.html.twig', [
                    'error' => 'User authentication required',
                    'programs' => []
                ]);
            }

            // Get user's active programs with exercise count
            $qb = $entityManager->createQueryBuilder();
            $programs = $qb->select('p', 'COUNT(te.id) as exerciseCount')
                ->from(TrainingProgram::class, 'p')
                ->leftJoin('p.training_exercises', 'te')
                ->where('p.users = :user')
                ->andWhere('p.is_active = :active')
                ->setParameter('user', $user)
                ->setParameter('active', true)
                ->groupBy('p.id')
                ->orderBy('p.created_at', 'DESC')
                ->getQuery()
                ->getResult();

            $formattedPrograms = [];
            foreach ($programs as $result) {
                $program = $result[0];
                $exerciseCount = $result['exerciseCount'];

                $formattedPrograms[] = [
                    'id' => $program->getId(),
                    'name' => $program->getName(),
                    'description' => $program->getDescription(),
                    'workoutsPerWeek' => $program->getWorkoutsPerWeek(),
                    'durationMinutes' => $program->getDurationMinutes(),
                    'difficultyLevel' => $program->getDifficultyLevel(),
                    'isActive' => $program->isActive(),
                    'exerciseCount' => (int)$exerciseCount,
                    'createdAt' => $program->getCreatedAt()?->format('d.m.Y'),
                ];
            }

            return $this->render('user_dashboard/my_programs/index.html.twig', [
                'programs' => $formattedPrograms
            ]);
        } catch (\Exception $e) {
            return $this->render('user_dashboard/my_programs/index.html.twig', [
                'error' => 'Error loading programs: ' . $e->getMessage(),
                'programs' => []
            ]);
        }
    }

    #[Route('/create', name: 'app_programs_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            try {
                $name = trim($request->request->get('name'));
                $description = trim($request->request->get('description'));
                $workoutsPerWeek = $request->request->get('workouts_per_week');
                $durationMinutes = $request->request->get('duration_minutes');
                $difficultyLevel = $request->request->get('difficulty_level');
                $selectedExercises = $request->request->all('selected_exercises') ?? [];

                // Validation
                if (empty($name)) {
                    $this->addFlash('error', 'Program name is required.');
                    return $this->redirectToRoute('app_programs_create');
                }

                if (strlen($name) < 3 || strlen($name) > 45) {
                    $this->addFlash('error', 'Program name must be between 3-45 characters.');
                    return $this->redirectToRoute('app_programs_create');
                }

                $program = new TrainingProgram();
                $program->setName($name);
                $program->setDescription($description ?: null);
                $program->setWorkoutsPerWeek($workoutsPerWeek ? (int)$workoutsPerWeek : null);
                $program->setDurationMinutes($durationMinutes ? (int)$durationMinutes : null);
                $program->setDifficultyLevel($difficultyLevel ?: null);
                $program->setUsers($this->getUser());

                // Add selected exercises
                if (!empty($selectedExercises)) {
                    $exerciseIds = array_map('intval', $selectedExercises);
                    $exercises = $entityManager->getRepository(TrainingExercises::class)
                        ->findBy(['id' => $exerciseIds]);

                    foreach ($exercises as $exercise) {
                        $program->addTrainingExercise($exercise);
                    }
                }

                $entityManager->persist($program);
                $entityManager->flush();

                $this->addFlash('success', 'Program created successfully!');
                return $this->redirectToRoute('app_programs_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error creating program: ' . $e->getMessage());
            }
        }

        // GET request - show create form
        $exercises = $entityManager->getRepository(TrainingExercises::class)
            ->findBy([], ['name' => 'ASC']);

        return $this->render('user_dashboard/my_programs/create.html.twig', [
            'exercises' => $exercises
        ]);
    }

    #[Route('/{id}/view', name: 'app_programs_view', methods: ['GET'])]
    public function view(TrainingProgram $program): Response
    {
        // Ensure user can only view their own programs
        if ($program->getUsers() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        return $this->render('user_dashboard/my_programs/view.html.twig', [
            'program' => $program
        ]);
    }

    #[Route('/{id}/edit', name: 'app_programs_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TrainingProgram $program, EntityManagerInterface $entityManager): Response
    {
        // Ensure user can only edit their own programs
        if ($program->getUsers() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        if ($request->isMethod('POST')) {
            try {
                $name = trim($request->request->get('name'));
                $description = trim($request->request->get('description'));
                $workoutsPerWeek = $request->request->get('workouts_per_week');
                $durationMinutes = $request->request->get('duration_minutes');
                $difficultyLevel = $request->request->get('difficulty_level');
                $selectedExercises = $request->request->all('selected_exercises') ?? [];

                // Validation
                if (empty($name)) {
                    $this->addFlash('error', 'Program name is required.');
                    return $this->redirectToRoute('app_programs_edit', ['id' => $program->getId()]);
                }

                $program->setName($name);
                $program->setDescription($description ?: null);
                $program->setWorkoutsPerWeek($workoutsPerWeek ? (int)$workoutsPerWeek : null);
                $program->setDurationMinutes($durationMinutes ? (int)$durationMinutes : null);
                $program->setDifficultyLevel($difficultyLevel ?: null);
                $program->setUpdatedAt(new \DateTimeImmutable());

                // Update exercises
                $program->getTrainingExercises()->clear();
                if (!empty($selectedExercises)) {
                    $exerciseIds = array_map('intval', $selectedExercises);
                    $exercises = $entityManager->getRepository(TrainingExercises::class)
                        ->findBy(['id' => $exerciseIds]);

                    foreach ($exercises as $exercise) {
                        $program->addTrainingExercise($exercise);
                    }
                }

                $entityManager->flush();

                $this->addFlash('success', 'Program updated successfully!');
                return $this->redirectToRoute('app_programs_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error updating program: ' . $e->getMessage());
            }
        }

        // GET request - show edit form
        $exercises = $entityManager->getRepository(TrainingExercises::class)
            ->findBy([], ['name' => 'ASC']);

        $selectedExerciseIds = $program->getTrainingExercises()->map(fn($ex) => $ex->getId())->toArray();

        return $this->render('user_dashboard/my_programs/edit.html.twig', [
            'program' => $program,
            'exercises' => $exercises,
            'selectedExerciseIds' => $selectedExerciseIds
        ]);
    }

    #[Route('/{id}/delete', name: 'app_programs_delete', methods: ['POST'])]
    public function delete(TrainingProgram $program, EntityManagerInterface $entityManager): Response
    {
        // Ensure user can only delete their own programs
        if ($program->getUsers() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access denied.');
        }

        try {
            // Check if program is being used in workout logs
            $workoutLogCount = $entityManager->createQueryBuilder()
                ->select('COUNT(wl.id)')
                ->from('App\Entity\WorkoutLog', 'wl')
                ->where('wl.training_program = :program')
                ->setParameter('program', $program)
                ->getQuery()
                ->getSingleScalarResult();

            if ($workoutLogCount > 0) {
                $this->addFlash('error', 'Cannot delete program that has been used in workouts.');
                return $this->redirectToRoute('app_programs_index');
            }

            $entityManager->remove($program);
            $entityManager->flush();

            $this->addFlash('success', 'Program deleted successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error deleting program: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_programs_index');
    }

    #[Route('/browse-shared', name: 'app_programs_browse_shared', methods: ['GET'])]
    public function browseSharedPrograms(EntityManagerInterface $entityManager): Response
    {
        try {
            $qb = $entityManager->createQueryBuilder();
            $programs = $qb->select('p', 'u', 'COUNT(te.id) as exerciseCount')
                ->from(TrainingProgram::class, 'p')
                ->join('p.users', 'u')
                ->leftJoin('p.training_exercises', 'te')
                ->where('p.is_public = :public')
                ->andWhere('p.is_active = :active')
                ->setParameter('public', true)
                ->setParameter('active', true)
                ->groupBy('p.id', 'u.id')
                ->orderBy('p.created_at', 'DESC')
                ->setMaxResults(20)
                ->getQuery()
                ->getResult();

            $formattedPrograms = [];
            foreach ($programs as $result) {
                $program = $result[0];
                $exerciseCount = $result['exerciseCount'];

                $formattedPrograms[] = [
                    'id' => $program->getId(),
                    'name' => $program->getName(),
                    'description' => $program->getDescription(),
                    'workoutsPerWeek' => $program->getWorkoutsPerWeek(),
                    'durationMinutes' => $program->getDurationMinutes(),
                    'difficultyLevel' => $program->getDifficultyLevel(),
                    'shareCode' => $program->getShareCode(),
                    'exerciseCount' => (int)$exerciseCount,
                    'createdAt' => $program->getCreatedAt()?->format('d.m.Y'),
                    'owner' => [
                        'name' => $program->getUsers()->getName(),
                        'surname' => $program->getUsers()->getSurname()
                    ]
                ];
            }

            return $this->render('user_dashboard/my_programs/browse_shared.html.twig', [
                'sharedPrograms' => $formattedPrograms
            ]);
        } catch (\Exception $e) {
            return $this->render('user_dashboard/my_programs/browse_shared.html.twig', [
                'sharedPrograms' => [],
                'error' => 'Error loading shared programs: ' . $e->getMessage()
            ]);
        }
    }
}
