<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Eğer kullanıcı giriş yapmışsa dashboard'a yönlendir
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard_overview');
        }

        return $this->render('home/index.html.twig');
    }
}
