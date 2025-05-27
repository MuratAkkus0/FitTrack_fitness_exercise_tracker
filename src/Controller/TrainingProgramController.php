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
                    'error' => 'Kullanıcı kimlik doğrulaması yapılmamış',
                    'programs' => []
                ]);
            }

            // Debug: Kullanıcı bilgilerini kontrol et
            $userId = $user instanceof \App\Entity\Users ? $user->getId() : 'unknown';
            $userEmail = $user instanceof \App\Entity\Users ? $user->getEmail() : 'unknown';

            // Önce tüm programları al
            $allPrograms = $entityManager->getRepository(TrainingProgram::class)->findAll();

            // Kullanıcıya ait tüm programları al (is_active filtresi olmadan)
            $userAllPrograms = $entityManager->getRepository(TrainingProgram::class)->findBy(
                ['users' => $user],
                ['created_at' => 'DESC']
            );

            // Sonra kullanıcıya ait aktif olanları filtrele
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
                    'exerciseCount' => count($program->getTrainingExercises()),
                    'createdAt' => $program->getCreatedAt()?->format('d.m.Y'),
                ];
            }

            $debugMessage = "Kullanıcı ID: $userId, Email: $userEmail, Toplam program: " . count($allPrograms) . ", Kullanıcı tüm programları: " . count($userAllPrograms) . ", Kullanıcı aktif programları: " . count($programs);

            return $this->render('user_dashboard/my_programs/index.html.twig', [
                'programs' => $formattedPrograms,
                'debug' => $debugMessage
            ]);
        } catch (\Exception $e) {
            return $this->render('user_dashboard/my_programs/index.html.twig', [
                'error' => 'Programlar yüklenirken hata oluştu: ' . $e->getMessage(),
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
                    'description' => $exercise->getDescription() ?? 'Açıklama yok',
                    'muscleGroup' => $exercise->getTargetMuscleGroup() ? $exercise->getTargetMuscleGroup()->value : 'Bilinmiyor'
                ];
            }, $exercises);

            return $this->json([
                'success' => true,
                'exercises' => $exerciseList,
                'count' => count($exerciseList),
                'debug' => 'Egzersizler başarıyla yüklendi'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Egzersizler yüklenirken hata oluştu: ' . $e->getMessage(),
                'debug' => $e->getTraceAsString()
            ], 500);
        }
    }

    #[Route('/create', name: 'app_programs_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validasyon
            if (empty($data['programName'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Program adı boş olamaz'
                ], 400);
            }

            if (strlen($data['programName']) < 3 || strlen($data['programName']) > 45) {
                return $this->json([
                    'success' => false,
                    'message' => 'Program adı 3-45 karakter arasında olmalıdır'
                ], 400);
            }

            $program = new TrainingProgram();
            $program->setName($data['programName']);
            $program->setDescription($data['programDescription'] ?? null);
            $program->setWorkoutsPerWeek($data['workoutsPerWeek'] ?? null);
            $program->setDurationMinutes($data['workoutDuration'] ?? null);
            $program->setDifficultyLevel($data['difficultyLevel'] ?? null);
            $program->setUsers($this->getUser());

            // Seçilen egzersizleri ekle
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
                'message' => 'Program başarıyla oluşturuldu',
                'program' => [
                    'id' => $program->getId(),
                    'name' => $program->getName(),
                    'description' => $program->getDescription()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Program oluşturulurken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/edit', name: 'app_programs_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TrainingProgram $program, EntityManagerInterface $entityManager): Response
    {
        // Kullanıcının sadece kendi programlarını düzenleyebilmesini sağla
        if ($program->getUsers() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Bu programa erişim yetkiniz yok.');
        }

        if ($request->isMethod('POST')) {
            try {
                $data = json_decode($request->getContent(), true);

                // Validasyon
                if (empty($data['programName'])) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Program adı boş olamaz'
                    ], 400);
                }

                $program->setName($data['programName']);
                $program->setDescription($data['programDescription'] ?? null);
                $program->setWorkoutsPerWeek($data['workoutsPerWeek'] ?? null);
                $program->setDurationMinutes($data['workoutDuration'] ?? null);
                $program->setDifficultyLevel($data['difficultyLevel'] ?? null);
                $program->setUpdatedAt(new \DateTimeImmutable());

                // Mevcut egzersizleri temizle
                $program->getTrainingExercises()->clear();

                // Yeni egzersizleri ekle
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
                    'message' => 'Program başarıyla güncellendi'
                ]);
            } catch (\Exception $e) {
                return $this->json([
                    'success' => false,
                    'message' => 'Program güncellenirken hata oluştu: ' . $e->getMessage()
                ], 500);
            }
        }

        // GET isteği için program verilerini döndür
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
            // Kullanıcının sadece kendi programlarını silebilmesini sağla
            if ($program->getUsers() !== $this->getUser()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Bu programa erişim yetkiniz yok.'
                ], 403);
            }

            // Soft delete - programı pasif yap
            $program->setIsActive(false);
            $program->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Program başarıyla silindi'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Program silinirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{id}/toggle-status', name: 'app_programs_toggle_status', methods: ['PATCH'])]
    public function toggleStatus(TrainingProgram $program, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Kullanıcının sadece kendi programlarını değiştirebilmesini sağla
            if ($program->getUsers() !== $this->getUser()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Bu programa erişim yetkiniz yok.'
                ], 403);
            }

            $program->setIsActive(!$program->isActive());
            $program->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Program durumu başarıyla değiştirildi',
                'isActive' => $program->isActive()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Program durumu değiştirilirken hata oluştu: ' . $e->getMessage()
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
                    'message' => 'Kullanıcı kimlik doğrulaması yapılmamış'
                ], 401);
            }

            // Kullanıcıya ait aktif programları al
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
                'message' => 'Programlar yüklenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/{programId}/add-exercise/{exerciseId}', name: 'app_programs_add_exercise', methods: ['POST'])]
    public function addExerciseToProgram(int $programId, int $exerciseId, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->getUser();

            // Programı kontrol et
            $program = $entityManager->getRepository(TrainingProgram::class)->find($programId);
            if (!$program) {
                return $this->json([
                    'success' => false,
                    'message' => 'Program bulunamadı'
                ], 404);
            }

            // Kullanıcının programa erişim yetkisi var mı kontrol et
            if ($program->getUsers() !== $user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Bu programa erişim yetkiniz yok'
                ], 403);
            }

            // Egzersizi kontrol et
            $exercise = $entityManager->getRepository(TrainingExercises::class)->find($exerciseId);
            if (!$exercise) {
                return $this->json([
                    'success' => false,
                    'message' => 'Egzersiz bulunamadı'
                ], 404);
            }

            // Egzersiz zaten programda var mı kontrol et
            if ($program->getTrainingExercises()->contains($exercise)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Bu egzersiz zaten programda mevcut'
                ], 400);
            }

            // Egzersizi programa ekle
            $program->addTrainingExercise($exercise);
            $program->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Egzersiz programa başarıyla eklendi',
                'exerciseCount' => count($program->getTrainingExercises())
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Egzersiz programa eklenirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}
