<?php

namespace App\Controller;

use App\Entity\Users;
use App\Enum\UserRoles;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Debug logs
            error_log("Registration: Plain password received: " . $plainPassword);

            // encode the plain password
            $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
            error_log("Registration: Hashed password: " . $hashedPassword);

            $user->setPassword($hashedPassword);
            $user->setRoles([UserRoles::USER->value]);

            $entityManager->persist($user);
            $entityManager->flush();

            // Test the password immediately after saving
            $isValid = $userPasswordHasher->isPasswordValid($user, $plainPassword);
            error_log("Registration: Password verification after save: " . ($isValid ? 'VALID' : 'INVALID'));

            // Redirect to login page after successful registration
            $this->addFlash('success', 'Your account has been successfully created. You can now log in.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
