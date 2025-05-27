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
            throw $this->createNotFoundException('Program bulunamadı veya erişim yetkiniz yok.');
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

            // Validasyon
            if (empty($data['programId'])) {
                return $this->json(['success' => false, 'message' => 'Program ID gerekli'], 400);
            }

            $program = $entityManager->getRepository(TrainingProgram::class)->find($data['programId']);
            if (!$program || $program->getUsers() !== $user) {
                return $this->json(['success' => false, 'message' => 'Program bulunamadı'], 404);
            }

            // Workout Log oluştur
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

            // Egzersiz detaylarını kaydet
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
                'message' => 'Antrenman başarıyla kaydedildi',
                'workoutId' => $workoutLog->getId()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Antrenman kaydedilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/history', name: 'app_workout_history', methods: ['GET'])]
    public function workoutHistory(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $workouts = $entityManager->getRepository(WorkoutLogs::class)->findBy(
            ['user' => $user],
            ['created_at' => 'DESC'],
            20 // Son 20 antrenman
        );

        $formattedWorkouts = [];
        foreach ($workouts as $workout) {
            $formattedWorkouts[] = [
                'id' => $workout->getId(),
                'programName' => $workout->getTrainingProgram()?->getName(),
                'date' => $workout->getCreatedAt()?->format('d.m.Y H:i'),
                'duration' => $workout->getDuration(),
                'isCompleted' => $workout->isCompleted(),
                'notes' => $workout->getNotes(),
                'exerciseCount' => count($workout->getWorkoutLogDetails()),
                'estimatedCalories' => $workout->getEstimatedCalories(),
                'totalVolume' => $workout->getTotalVolume(),
                'totalReps' => $workout->getTotalReps()
            ];
        }

        return $this->render('user_dashboard/workout/history.html.twig', [
            'workouts' => $formattedWorkouts
        ]);
    }

    #[Route('/details/{id}', name: 'app_workout_details', methods: ['GET'])]
    public function workoutDetails(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $workout = $entityManager->getRepository(WorkoutLogs::class)->find($id);

        if (!$workout || $workout->getUser() !== $user) {
            throw $this->createNotFoundException('Antrenman bulunamadı veya erişim yetkiniz yok.');
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

        // Bugünün antrenmanlarını getir
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        $todaysWorkouts = $entityManager->getRepository(WorkoutLogs::class)
            ->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.created_at >= :today')
            ->andWhere('w.created_at < :tomorrow')
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getResult();

        // Kullanıcının aktif programlarını getir
        $activePrograms = $entityManager->getRepository(TrainingProgram::class)->findBy(
            ['users' => $user, 'is_active' => true]
        );

        return $this->render('user_dashboard/todays_workout/index.html.twig', [
            'todaysWorkouts' => $todaysWorkouts,
            'activePrograms' => $activePrograms
        ]);
    }
}
