<?php

namespace App\Controller;

use App\Entity\TrainingProgram;
use App\Entity\TrainingExercises;
use App\Form\TrainingProgramFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
                    'error' => 'User authentication not performed',
                    'programs' => []
                ]);
            }

            // Debug: Check user information
            $userId = $user instanceof \App\Entity\Users ? $user->getId() : 'unknown';
            $userEmail = $user instanceof \App\Entity\Users ? $user->getEmail() : 'unknown';

            // First get all programs
            $allPrograms = $entityManager->getRepository(TrainingProgram::class)->findAll();

            // Get all programs belonging to the user (without is_active filter)
            $userAllPrograms = $entityManager->getRepository(TrainingProgram::class)->findBy(
                ['users' => $user],
                ['created_at' => 'DESC']
            );

            // Then filter active ones belonging to the user
            $programs = $entityManager->getRepository(TrainingProgram::class)->findBy(
                ['users' => $user, 'is_active' => true],
                ['created_at' => 'DESC']
            );

            $formattedPrograms = [];
            foreach ($programs as $program) {
                $formattedPrograms[] = [
                    'id' => $program->getId(),
                    'name' => $program->getName(),
                    'description' => $program->getDescription(),
                    'workoutsPerWeek' => $program->getWorkoutsPerWeek(),
                    'durationMinutes' => $program->getDurationMinutes(),
                    'difficultyLevel' => $program->getDifficultyLevel(),
                    'isActive' => $program->isActive(),
                    'isPublic' => $program->isPublic(),
                    'shareCode' => $program->getShareCode(),
                    'exerciseCount' => count($program->getTrainingExercises()),
                    'createdAt' => $program->getCreatedAt()?->format('d.m.Y'),
                ];
            }

            $debugMessage = "User ID: $userId, Email: $userEmail, Total programs: " . count($allPrograms) . ", User all programs: " . count($userAllPrograms) . ", User active programs: " . count($programs);

            return $this->render('user_dashboard/my_programs/index.html.twig', [
                'programs' => $formattedPrograms,
                'debug' => $debugMessage
            ]);
        } catch (\Exception $e) {
            return $this->render('user_dashboard/my_programs/index.html.twig', [
                'error' => 'An error occurred while loading programs: ' . $e->getMessage(),
                'programs' => []
            ]);
        }
    }

    #[Route('/exercises', name: 'app_programs_exercises_api', methods: ['GET'])]
    public function getExercises(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $exercises = $entityManager->getRepository(TrainingExercises::class)->findAll();

            $exerciseList = array_map(function ($exercise) {
                return [
                    'id' => $exercise->getId(),
                    'name' => $exercise->getName(),
                    'description' => $exercise->getDescription() ?? 'No description',
                    'muscleGroup' => $exercise->getTargetMuscleGroup() ? $exercise->getTargetMuscleGroup()->value : 'Unknown'
                ];
            }, $exercises);

            return $this->json([
                'success' => true,
                'exercises' => $exerciseList,
                'count' => count($exerciseList),
                'debug' => 'Exercises loaded successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while loading exercises: ' . $e->getMessage(),
                'debug' => $e->getTraceAsString()
            ], 500);
        }
    }

    #[Route('/create', name: 'app_programs_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation
            if (empty($data['programName'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Program name cannot be empty'
                ], 400);
            }

            if (strlen($data['programName']) < 3 || strlen($data['programName']) > 45) {
                return $this->json([
                    'success' => false,
                    'message' => 'Program name must be between 3-45 characters'
                ], 400);
            }

            $program = new TrainingProgram();
            $program->setName($data['programName']);
            $program->setDescription($data['programDescription'] ?? null);
            $program->setWorkoutsPerWeek($data['workoutsPerWeek'] ?? null);
            $program->setDurationMinutes($data['workoutDuration'] ?? null);
            $program->setDifficultyLevel($data['difficultyLevel'] ?? null);
            $program->setUsers($this->getUser());

            // Add selected exercises
            if (isset($data['selectedExercises']) && is_array($data['selectedExercises'])) {
                foreach ($data['selectedExercises'] as $exerciseId) {
                    $exercise = $entityManager->getRepository(TrainingExercises::class)->find($exerciseId);
                    if ($exercise) {
                        $program->addTrainingExercise($exercise);
                    }
                }
            }

            $entityManager->persist($program);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Program created successfully',
                'program' => [
                    'id' => $program->getId(),
                    'name' => $program->getName(),
                    'description' => $program->getDescription()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while creating the program: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/edit', name: 'app_programs_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TrainingProgram $program, EntityManagerInterface $entityManager): Response
    {
        // Ensure user can only edit their own programs
        if ($program->getUsers() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have access to this program.');
        }

        if ($request->isMethod('POST')) {
            try {
                $data = json_decode($request->getContent(), true);

                // Validation
                if (empty($data['programName'])) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Program name cannot be empty'
                    ], 400);
                }

                $program->setName($data['programName']);
                $program->setDescription($data['programDescription'] ?? null);
                $program->setWorkoutsPerWeek($data['workoutsPerWeek'] ?? null);
                $program->setDurationMinutes($data['workoutDuration'] ?? null);
                $program->setDifficultyLevel($data['difficultyLevel'] ?? null);
                $program->setUpdatedAt(new \DateTimeImmutable());

                // Clear existing exercises
                $program->getTrainingExercises()->clear();

                // Add new exercises
                if (isset($data['selectedExercises']) && is_array($data['selectedExercises'])) {
                    foreach ($data['selectedExercises'] as $exerciseId) {
                        $exercise = $entityManager->getRepository(TrainingExercises::class)->find($exerciseId);
                        if ($exercise) {
                            $program->addTrainingExercise($exercise);
                        }
                    }
                }

                $entityManager->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Program updated successfully'
                ]);
            } catch (\Exception $e) {
                return $this->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the program: ' . $e->getMessage()
                ], 500);
            }
        }

        // GET request, return program data
        $programData = [
            'id' => $program->getId(),
            'name' => $program->getName(),
            'description' => $program->getDescription(),
            'workoutsPerWeek' => $program->getWorkoutsPerWeek(),
            'durationMinutes' => $program->getDurationMinutes(),
            'difficultyLevel' => $program->getDifficultyLevel(),
            'selectedExercises' => $program->getTrainingExercises()->map(fn($exercise) => $exercise->getId())->toArray()
        ];

        return $this->json([
            'success' => true,
            'program' => $programData
        ]);
    }

    #[Route('/{id}/delete', name: 'app_programs_delete', methods: ['DELETE'])]
    public function delete(TrainingProgram $program, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Ensure user can only delete their own programs
            if ($program->getUsers() !== $this->getUser()) {
                return $this->json([
                    'success' => false,
                    'message' => 'You do not have access to delete this program.'
                ], 403);
            }

            // Soft delete - mark program as inactive
            $program->setIsActive(false);
            $program->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Program deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while deleting the program: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/toggle-status', name: 'app_programs_toggle_status', methods: ['PATCH'])]
    public function toggleStatus(TrainingProgram $program, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Ensure user can only toggle their own programs
            if ($program->getUsers() !== $this->getUser()) {
                return $this->json([
                    'success' => false,
                    'message' => 'You do not have access to toggle this program.'
                ], 403);
            }

            $program->setIsActive(!$program->isActive());
            $program->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Program status toggled successfully',
                'isActive' => $program->isActive()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while toggling the program status: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/user-programs', name: 'app_programs_api', methods: ['GET'])]
    public function getUserPrograms(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'User authentication not performed'
                ], 401);
            }

            // Get active programs belonging to the user
            $programs = $entityManager->getRepository(TrainingProgram::class)->findBy(
                ['users' => $user, 'is_active' => true],
                ['created_at' => 'DESC']
            );

            $formattedPrograms = [];
            foreach ($programs as $program) {
                $formattedPrograms[] = [
                    'id' => $program->getId(),
                    'name' => $program->getName(),
                    'description' => $program->getDescription(),
                    'workoutsPerWeek' => $program->getWorkoutsPerWeek(),
                    'durationMinutes' => $program->getDurationMinutes(),
                    'difficultyLevel' => $program->getDifficultyLevel(),
                    'exerciseCount' => count($program->getTrainingExercises()),
                    'createdAt' => $program->getCreatedAt()?->format('d.m.Y'),
                ];
            }

            return $this->json([
                'success' => true,
                'programs' => $formattedPrograms,
                'count' => count($formattedPrograms)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while loading programs: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{programId}/add-exercise/{exerciseId}', name: 'app_programs_add_exercise', methods: ['POST'])]
    public function addExerciseToProgram(int $programId, int $exerciseId, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->getUser();

            // Check program
            $program = $entityManager->getRepository(TrainingProgram::class)->find($programId);
            if (!$program) {
                return $this->json([
                    'success' => false,
                    'message' => 'Program not found'
                ], 404);
            }

            // Check user access
            if ($program->getUsers() !== $user) {
                return $this->json([
                    'success' => false,
                    'message' => 'You do not have access to add exercise to this program'
                ], 403);
            }

            // Check exercise
            $exercise = $entityManager->getRepository(TrainingExercises::class)->find($exerciseId);
            if (!$exercise) {
                return $this->json([
                    'success' => false,
                    'message' => 'Exercise not found'
                ], 404);
            }

            // Check if exercise already exists in program
            if ($program->getTrainingExercises()->contains($exercise)) {
                return $this->json([
                    'success' => false,
                    'message' => 'This exercise already exists in the program'
                ], 400);
            }

            // Add exercise to program
            $program->addTrainingExercise($exercise);
            $program->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Exercise added to program successfully',
                'exerciseCount' => count($program->getTrainingExercises())
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while adding exercise to program: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/share', name: 'app_programs_share', methods: ['POST'])]
    public function shareProgram(TrainingProgram $program, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Ensure user can only share their own programs
            if ($program->getUsers() !== $this->getUser()) {
                return $this->json([
                    'success' => false,
                    'message' => 'You do not have access to share this program.'
                ], 403);
            }

            // If already has a share code, use it
            if (!$program->getShareCode()) {
                // Generate unique share code
                do {
                    $shareCode = strtoupper(substr(md5(uniqid()), 0, 8));
                    $existingProgram = $entityManager->getRepository(TrainingProgram::class)
                        ->findOneBy(['share_code' => $shareCode]);
                } while ($existingProgram);

                $program->setShareCode($shareCode);
            }

            $program->setIsPublic(true);
            $program->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Program shared successfully',
                'shareCode' => $program->getShareCode(),
                'shareUrl' => $this->generateUrl('app_programs_shared', ['shareCode' => $program->getShareCode()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while sharing the program: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/unshare', name: 'app_programs_unshare', methods: ['POST'])]
    public function unshareProgram(TrainingProgram $program, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Ensure user can only unshare their own programs
            if ($program->getUsers() !== $this->getUser()) {
                return $this->json([
                    'success' => false,
                    'message' => 'You do not have access to unshare this program.'
                ], 403);
            }

            $program->setIsPublic(false);
            $program->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Program unshared successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while unsharing the program: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/shared/{shareCode}', name: 'app_programs_shared', methods: ['GET'])]
    public function getSharedProgram(string $shareCode, EntityManagerInterface $entityManager): Response
    {
        $program = $entityManager->getRepository(TrainingProgram::class)
            ->findOneBy(['share_code' => $shareCode, 'is_public' => true]);

        if (!$program) {
            throw $this->createNotFoundException('Shared program not found or no longer shared.');
        }

        return $this->render('user_dashboard/my_programs/shared.html.twig', [
            'program' => $program,
            'exercises' => $program->getTrainingExercises(),
            'owner' => $program->getUsers()
        ]);
    }

    #[Route('/shared/{shareCode}/copy', name: 'app_programs_copy_shared', methods: ['POST'])]
    public function copySharedProgram(string $shareCode, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->getUser();
            $originalProgram = $entityManager->getRepository(TrainingProgram::class)
                ->findOneBy(['share_code' => $shareCode, 'is_public' => true]);

            if (!$originalProgram) {
                return $this->json([
                    'success' => false,
                    'message' => 'Shared program not found'
                ], 404);
            }

            // Check if user already has a copy of this program
            $existingCopy = $entityManager->getRepository(TrainingProgram::class)
                ->findOneBy([
                    'users' => $user,
                    'name' => $originalProgram->getName() . ' (Copy)',
                    'is_active' => true
                ]);

            if ($existingCopy) {
                return $this->json([
                    'success' => false,
                    'message' => 'This program is already copied'
                ], 400);
            }

            // Create new program
            $newProgram = new TrainingProgram();
            $newProgram->setName($originalProgram->getName() . ' (Copy)');
            $newProgram->setDescription($originalProgram->getDescription() . ' - ' . $originalProgram->getUsers()->getName() . ' ' . $originalProgram->getUsers()->getSurname() . ' shared');
            $newProgram->setWorkoutsPerWeek($originalProgram->getWorkoutsPerWeek());
            $newProgram->setDurationMinutes($originalProgram->getDurationMinutes());
            $newProgram->setDifficultyLevel($originalProgram->getDifficultyLevel());
            $newProgram->setUsers($user);
            $newProgram->setIsPublic(false); // Copy defaults to private

            // Copy exercises
            foreach ($originalProgram->getTrainingExercises() as $exercise) {
                $newProgram->addTrainingExercise($exercise);
            }

            $entityManager->persist($newProgram);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Program copied successfully',
                'program' => [
                    'id' => $newProgram->getId(),
                    'name' => $newProgram->getName()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An error occurred while copying the program: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/browse-shared', name: 'app_programs_browse_shared', methods: ['GET'])]
    public function browseSharedPrograms(EntityManagerInterface $entityManager): Response
    {
        $sharedPrograms = $entityManager->getRepository(TrainingProgram::class)
            ->findBy(['is_public' => true, 'is_active' => true], ['created_at' => 'DESC']);

        $formattedPrograms = [];
        foreach ($sharedPrograms as $program) {
            $formattedPrograms[] = [
                'id' => $program->getId(),
                'name' => $program->getName(),
                'description' => $program->getDescription(),
                'workoutsPerWeek' => $program->getWorkoutsPerWeek(),
                'durationMinutes' => $program->getDurationMinutes(),
                'difficultyLevel' => $program->getDifficultyLevel(),
                'exerciseCount' => count($program->getTrainingExercises()),
                'createdAt' => $program->getCreatedAt()?->format('d.m.Y'),
                'shareCode' => $program->getShareCode(),
                'owner' => [
                    'name' => $program->getUsers()->getName(),
                    'surname' => $program->getUsers()->getSurname()
                ]
            ];
        }

        return $this->render('user_dashboard/my_programs/browse_shared.html.twig', [
            'sharedPrograms' => $formattedPrograms
        ]);
    }
}
