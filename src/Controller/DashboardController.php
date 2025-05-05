<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{

  #[Route('/', 'app_dashboard_overview')]
  public function index(): Response
  {
    return $this->render('user_dashboard/index.html.twig');
  }
  #[Route('/today')]
  public function todaysWorkout(): Response
  {
    return $this->render('user_dashboard/todays_workout/index.html.twig');
  }
  #[Route('/my-programs')]
  public function programs(): Response
  {
    return $this->render('user_dashboard/my_programs/index.html.twig');
  }
  #[Route('/exercise-library')]
  public function exerciseLibrary(): Response
  {
    return $this->render('user_dashboard/exercise_library/index.html.twig');
  }
}
