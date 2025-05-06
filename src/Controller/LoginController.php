<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: "/test", name: "")]

class LoginController extends AbstractController
{
    #[Route(path: "/", name: "")]
    public function index(): Response
    {
        return $this->render("login-register-pages/register.html.twig");
    }
}
