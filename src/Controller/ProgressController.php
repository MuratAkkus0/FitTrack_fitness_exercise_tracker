<?php

namespace App\Controller;

use App\Entity\WorkoutLogs;
use App\Entity\TrainingProgram;
use App\Entity\TrainingExercises;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/progress')]
#[IsGranted('ROLE_USER')]
class ProgressController extends AbstractController
{
    #[Route('/', name: 'app_progress_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Temel istatistikleri hesapla
        $stats = $this->calculateUserStats($entityManager, $user);

        return $this->render('user_dashboard/progress/index.html.twig', [
            'stats' => $stats
        ]);
    }

    #[Route('/charts/data', name: 'app_progress_charts_data', methods: ['GET'])]
    public function getChartsData(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        // Son 30 günün antrenman verilerini getir
        $thirtyDaysAgo = new \DateTime('-30 days');

        $workouts = $entityManager->getRepository(WorkoutLogs::class)
            ->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.created_at >= :thirtyDaysAgo')
            ->andWhere('w.is_completed = true')
            ->setParameter('user', $user)
            ->setParameter('thirtyDaysAgo', $thirtyDaysAgo)
            ->orderBy('w.created_at', 'ASC')
            ->getQuery()
            ->getResult();

        // Günlük antrenman sayısı grafiği için veri hazırla
        $dailyWorkouts = [];
        $workoutDurations = [];

        foreach ($workouts as $workout) {
            $date = $workout->getCreatedAt()->format('Y-m-d');

            if (!isset($dailyWorkouts[$date])) {
                $dailyWorkouts[$date] = 0;
                $workoutDurations[$date] = 0;
            }

            $dailyWorkouts[$date]++;
            $workoutDurations[$date] += (float) $workout->getDuration();
        }

        // Haftalık ilerleme verisi
        $weeklyProgress = $this->calculateWeeklyProgress($entityManager, $user);

        // En çok yapılan egzersizler
        $topExercises = $this->getTopExercises($entityManager, $user);

        return $this->json([
            'dailyWorkouts' => $dailyWorkouts,
            'workoutDurations' => $workoutDurations,
            'weeklyProgress' => $weeklyProgress,
            'topExercises' => $topExercises
        ]);
    }

    #[Route('/exercise/{exerciseId}/progress', name: 'app_progress_exercise', methods: ['GET'])]
    public function exerciseProgress(int $exerciseId, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $exercise = $entityManager->getRepository(TrainingExercises::class)->find($exerciseId);

        if (!$exercise) {
            return $this->json(['error' => 'Egzersiz bulunamadı'], 404);
        }

        // Bu egzersiz için son 3 ayın verilerini getir
        $threeMonthsAgo = new \DateTime('-3 months');

        $exerciseData = $entityManager->createQueryBuilder()
            ->select('wld.weight, wld.reps, wld.sets, wl.created_at')
            ->from('App\Entity\WorkoutLogDetails', 'wld')
            ->join('wld.workoutLog', 'wl')
            ->where('wl.user = :user')
            ->andWhere('wld.exercise = :exercise')
            ->andWhere('wl.created_at >= :threeMonthsAgo')
            ->andWhere('wl.is_completed = true')
            ->setParameter('user', $user)
            ->setParameter('exercise', $exercise)
            ->setParameter('threeMonthsAgo', $threeMonthsAgo)
            ->orderBy('wl.created_at', 'ASC')
            ->getQuery()
            ->getResult();

        $progressData = [];
        foreach ($exerciseData as $data) {
            $progressData[] = [
                'date' => $data['created_at']->format('Y-m-d'),
                'weight' => (float) $data['weight'],
                'reps' => $data['reps'],
                'sets' => $data['sets'],
                'volume' => (float) $data['weight'] * $data['reps'] * $data['sets']
            ];
        }

        return $this->json([
            'exercise' => [
                'id' => $exercise->getId(),
                'name' => $exercise->getName(),
                'muscleGroup' => $exercise->getTargetMuscleGroup()?->value
            ],
            'progressData' => $progressData
        ]);
    }

    #[Route('/reports/weekly', name: 'app_progress_weekly_report', methods: ['GET'])]
    public function weeklyReport(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Bu haftanın başlangıcı
        $weekStart = new \DateTime('monday this week');
        $weekEnd = new \DateTime('sunday this week');

        $weeklyWorkouts = $entityManager->getRepository(WorkoutLogs::class)
            ->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.created_at >= :weekStart')
            ->andWhere('w.created_at <= :weekEnd')
            ->setParameter('user', $user)
            ->setParameter('weekStart', $weekStart)
            ->setParameter('weekEnd', $weekEnd)
            ->getQuery()
            ->getResult();

        $weeklyStats = [
            'totalWorkouts' => count($weeklyWorkouts),
            'totalDuration' => array_sum(array_map(fn($w) => (float) $w->getDuration(), $weeklyWorkouts)),
            'completedWorkouts' => count(array_filter($weeklyWorkouts, fn($w) => $w->isCompleted())),
            'averageDuration' => count($weeklyWorkouts) > 0 ?
                array_sum(array_map(fn($w) => (float) $w->getDuration(), $weeklyWorkouts)) / count($weeklyWorkouts) : 0
        ];

        return $this->render('user_dashboard/progress/weekly_report.html.twig', [
            'weeklyStats' => $weeklyStats,
            'weeklyWorkouts' => $weeklyWorkouts,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd
        ]);
    }

    private function calculateUserStats(EntityManagerInterface $entityManager, $user): array
    {
        // Toplam antrenman sayısı
        $totalWorkouts = $entityManager->getRepository(WorkoutLogs::class)
            ->count(['user' => $user, 'is_completed' => true]);

        // Toplam antrenman süresi
        $totalDuration = $entityManager->createQueryBuilder()
            ->select('SUM(w.duration)')
            ->from('App\Entity\WorkoutLogs', 'w')
            ->where('w.user = :user')
            ->andWhere('w.is_completed = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // Bu ayki antrenman sayısı
        $thisMonthStart = new \DateTime('first day of this month');
        $thisMonthWorkouts = $entityManager->getRepository(WorkoutLogs::class)
            ->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.user = :user')
            ->andWhere('w.created_at >= :thisMonthStart')
            ->andWhere('w.is_completed = true')
            ->setParameter('user', $user)
            ->setParameter('thisMonthStart', $thisMonthStart)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // Aktif program sayısı
        $activePrograms = $entityManager->getRepository(TrainingProgram::class)
            ->count(['users' => $user, 'is_active' => true]);

        return [
            'totalWorkouts' => $totalWorkouts,
            'totalDuration' => round((float) $totalDuration, 2),
            'thisMonthWorkouts' => $thisMonthWorkouts,
            'activePrograms' => $activePrograms,
            'averageDuration' => $totalWorkouts > 0 ? round((float) $totalDuration / $totalWorkouts, 2) : 0
        ];
    }

    private function calculateWeeklyProgress(EntityManagerInterface $entityManager, $user): array
    {
        $weeklyData = [];

        for ($i = 6; $i >= 0; $i--) {
            $weekStart = new \DateTime("-{$i} weeks monday");
            $weekEnd = new \DateTime("-{$i} weeks sunday");

            $weekWorkouts = $entityManager->getRepository(WorkoutLogs::class)
                ->createQueryBuilder('w')
                ->select('COUNT(w.id)')
                ->where('w.user = :user')
                ->andWhere('w.created_at >= :weekStart')
                ->andWhere('w.created_at <= :weekEnd')
                ->andWhere('w.is_completed = true')
                ->setParameter('user', $user)
                ->setParameter('weekStart', $weekStart)
                ->setParameter('weekEnd', $weekEnd)
                ->getQuery()
                ->getSingleScalarResult() ?? 0;

            $weeklyData[] = [
                'week' => $weekStart->format('M d'),
                'workouts' => $weekWorkouts
            ];
        }

        return $weeklyData;
    }

    private function getTopExercises(EntityManagerInterface $entityManager, $user): array
    {
        $topExercises = $entityManager->createQueryBuilder()
            ->select('e.name, COUNT(wld.id) as exercise_count')
            ->from('App\Entity\WorkoutLogDetails', 'wld')
            ->join('wld.exercise', 'e')
            ->join('wld.workoutLog', 'wl')
            ->where('wl.user = :user')
            ->andWhere('wl.is_completed = true')
            ->setParameter('user', $user)
            ->groupBy('e.id')
            ->orderBy('exercise_count', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $topExercises;
    }
}
