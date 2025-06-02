<?php

namespace App\Controller;

use App\Entity\WorkoutLogs;
use App\Entity\TrainingProgram;
use App\Entity\FitnessGoal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

#[Route('/dashboard/progress')]
#[IsGranted('ROLE_USER')]
class ProgressController extends AbstractController
{
    #[Route('/', name: 'app_progress_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Main stats
        $stats = $this->getMainStats($entityManager, $user);

        // Weekly progress (last 4 weeks)
        $weeklyProgress = $this->getWeeklyProgress($entityManager, $user);

        // Monthly summary
        $monthlyStats = $this->getMonthlyStats($entityManager, $user);

        // Top exercises
        $topExercises = $this->getTopExercises($entityManager, $user);

        // Recent workouts for progress tracking
        $recentWorkouts = $this->getRecentWorkouts($entityManager, $user);

        return $this->render('user_dashboard/progress/index.html.twig', [
            'stats' => $stats,
            'weeklyProgress' => $weeklyProgress,
            'monthlyStats' => $monthlyStats,
            'topExercises' => $topExercises,
            'recentWorkouts' => $recentWorkouts
        ]);
    }

    #[Route('/charts/data', name: 'app_progress_charts_data', methods: ['GET'])]
    public function getChartsData(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        // Get workout data for charts
        $workoutData = $entityManager->createQueryBuilder()
            ->select('DATE(wl.workout_date) as date, COUNT(wl.id) as count')
            ->from(WorkoutLogs::class, 'wl')
            ->where('wl.user = :user')
            ->andWhere('wl.workout_date >= :thirtyDaysAgo')
            ->setParameter('user', $user)
            ->setParameter('thirtyDaysAgo', new \DateTime('-30 days'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json([
            'workoutData' => $workoutData
        ]);
    }

    #[Route('/performance-report', name: 'app_progress_performance_report', methods: ['POST'])]
    public function performanceReport(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Log performance data if needed (optional)
            // For now, just acknowledge receipt
            return $this->json([
                'success' => true,
                'message' => 'Performance report received'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error processing performance report'
            ], 500);
        }
    }

    private function getMainStats(EntityManagerInterface $entityManager, $user): array
    {
        // Total completed workouts
        $totalWorkouts = $entityManager->getRepository(WorkoutLogs::class)
            ->count(['user' => $user, 'is_completed' => true]);

        // Total workout time
        $totalDuration = $entityManager->createQueryBuilder()
            ->select('SUM(w.duration)')
            ->from(WorkoutLogs::class, 'w')
            ->where('w.user = :user AND w.is_completed = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // This month's workouts
        $thisMonth = new \DateTime('first day of this month');
        $thisMonthWorkouts = $entityManager->getRepository(WorkoutLogs::class)
            ->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.user = :user')
            ->andWhere('w.created_at >= :thisMonth')
            ->andWhere('w.is_completed = true')
            ->setParameter('user', $user)
            ->setParameter('thisMonth', $thisMonth)
            ->getQuery()
            ->getSingleScalarResult();

        // Active programs
        $activePrograms = $entityManager->getRepository(TrainingProgram::class)
            ->count(['users' => $user, 'is_active' => true]);

        // Active goals
        $activeGoals = $entityManager->getRepository(FitnessGoal::class)
            ->count(['user' => $user, 'is_active' => true, 'is_completed' => false]);

        return [
            'totalWorkouts' => $totalWorkouts,
            'totalDuration' => round($totalDuration, 1),
            'thisMonthWorkouts' => $thisMonthWorkouts,
            'activePrograms' => $activePrograms,
            'activeGoals' => $activeGoals,
            'averageDuration' => $totalWorkouts > 0 ? round($totalDuration / $totalWorkouts, 1) : 0
        ];
    }

    private function getWeeklyProgress(EntityManagerInterface $entityManager, $user): array
    {
        $weeklyData = [];

        for ($i = 3; $i >= 0; $i--) {
            $weekStart = new \DateTime("-{$i} weeks monday");
            $weekEnd = new \DateTime("-{$i} weeks sunday 23:59:59");

            $workoutCount = $entityManager->getRepository(WorkoutLogs::class)
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
                ->getSingleScalarResult();

            $weeklyData[] = [
                'week' => $weekStart->format('M j'),
                'workouts' => (int) $workoutCount
            ];
        }

        return $weeklyData;
    }

    private function getMonthlyStats(EntityManagerInterface $entityManager, $user): array
    {
        $currentMonth = new \DateTime('first day of this month');
        $lastMonth = new \DateTime('first day of last month');
        $lastMonthEnd = new \DateTime('last day of last month 23:59:59');

        // This month
        $thisMonthWorkouts = $entityManager->getRepository(WorkoutLogs::class)
            ->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.user = :user')
            ->andWhere('w.created_at >= :thisMonth')
            ->andWhere('w.is_completed = true')
            ->setParameter('user', $user)
            ->setParameter('thisMonth', $currentMonth)
            ->getQuery()
            ->getSingleScalarResult();

        // Last month
        $lastMonthWorkouts = $entityManager->getRepository(WorkoutLogs::class)
            ->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.user = :user')
            ->andWhere('w.created_at >= :lastMonth')
            ->andWhere('w.created_at <= :lastMonthEnd')
            ->andWhere('w.is_completed = true')
            ->setParameter('user', $user)
            ->setParameter('lastMonth', $lastMonth)
            ->setParameter('lastMonthEnd', $lastMonthEnd)
            ->getQuery()
            ->getSingleScalarResult();

        $improvement = $lastMonthWorkouts > 0 ?
            round((($thisMonthWorkouts - $lastMonthWorkouts) / $lastMonthWorkouts) * 100, 1) : 0;

        return [
            'thisMonth' => (int) $thisMonthWorkouts,
            'lastMonth' => (int) $lastMonthWorkouts,
            'improvement' => $improvement
        ];
    }

    private function getTopExercises(EntityManagerInterface $entityManager, $user): array
    {
        return $entityManager->createQueryBuilder()
            ->select('e.name, COUNT(wld.id) as count')
            ->from('App\Entity\WorkoutLogDetails', 'wld')
            ->join('wld.exercise', 'e')
            ->join('wld.workoutLog', 'wl')
            ->where('wl.user = :user')
            ->andWhere('wl.is_completed = true')
            ->setParameter('user', $user)
            ->groupBy('e.id')
            ->orderBy('count', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    private function getRecentWorkouts(EntityManagerInterface $entityManager, $user): array
    {
        return $entityManager->getRepository(WorkoutLogs::class)->findBy(
            ['user' => $user, 'is_completed' => true],
            ['created_at' => 'DESC'],
            5
        );
    }
}
