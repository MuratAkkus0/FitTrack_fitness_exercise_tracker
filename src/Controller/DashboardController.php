<?php

namespace App\Controller;

use App\Entity\TrainingProgram;
use App\Entity\WorkoutLogs;
use App\Entity\FitnessGoal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
  #[Route('/', name: 'app_dashboard_overview')]
  public function index(EntityManagerInterface $entityManager): Response
  {
    $user = $this->getUser();

    // Kullanıcının aktif programları
    $activePrograms = $entityManager->getRepository(TrainingProgram::class)->findBy(
      ['users' => $user, 'is_active' => true],
      ['created_at' => 'DESC'],
      5
    );

    // Son antrenmanlar
    $recentWorkouts = $entityManager->getRepository(WorkoutLogs::class)->findBy(
      ['user' => $user],
      ['created_at' => 'DESC'],
      5
    );

    // Bu haftaki antrenmanlar
    $weekStart = new \DateTime('monday this week');
    $weekEnd = new \DateTime('sunday this week 23:59:59');

    $thisWeekWorkouts = $entityManager->getRepository(WorkoutLogs::class)
      ->createQueryBuilder('w')
      ->where('w.user = :user')
      ->andWhere('w.created_at >= :weekStart')
      ->andWhere('w.created_at <= :weekEnd')
      ->setParameter('user', $user)
      ->setParameter('weekStart', $weekStart)
      ->setParameter('weekEnd', $weekEnd)
      ->getQuery()
      ->getResult();

    // Aktif hedefler
    $activeGoals = $entityManager->getRepository(FitnessGoal::class)->findBy(
      ['user' => $user, 'is_active' => true, 'is_completed' => false],
      ['created_at' => 'DESC'],
      3
    );

    // İstatistikler
    $totalWorkouts = $entityManager->getRepository(WorkoutLogs::class)->count(['user' => $user]);
    $totalPrograms = $entityManager->getRepository(TrainingProgram::class)->count(['users' => $user]);
    $completedGoals = $entityManager->getRepository(FitnessGoal::class)->count([
      'user' => $user,
      'is_completed' => true
    ]);

    // Bu ayın antrenman sayısı
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

    return $this->render('user_dashboard/index.html.twig', [
      'activePrograms' => $activePrograms,
      'recentWorkouts' => $recentWorkouts,
      'thisWeekWorkouts' => $thisWeekWorkouts,
      'activeGoals' => $activeGoals,
      'stats' => [
        'totalWorkouts' => $totalWorkouts,
        'totalPrograms' => $totalPrograms,
        'completedGoals' => $completedGoals,
        'thisMonthWorkouts' => $thisMonthWorkouts,
        'thisWeekCount' => count($thisWeekWorkouts)
      ]
    ]);
  }

  #[Route('/today', name: 'app_dashboard_today')]
  public function todaysWorkout(): Response
  {
    return $this->redirectToRoute('app_workout_today');
  }
}
