<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Repository\TrainingProgramRepository;
use App\Repository\TrainingExercisesRepository;
use App\Repository\WorkoutLogsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function index(
        UsersRepository $usersRepository,
        TrainingProgramRepository $programRepository,
        TrainingExercisesRepository $exercisesRepository,
        WorkoutLogsRepository $logsRepository
    ): Response {
        // Sadece admin rolüne sahip kullanıcılar erişebilir
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // İstatistikleri hazırla
        $usersCount = count($usersRepository->findAll());
        $programsCount = count($programRepository->findAll());
        $exercisesCount = count($exercisesRepository->findAll());
        $workoutsCount = count($logsRepository->findBy(['is_completed' => true]));

        return $this->render('admin/dashboard.html.twig', [
            'users_count' => $usersCount,
            'programs_count' => $programsCount,
            'exercises_count' => $exercisesCount,
            'workouts_count' => $workoutsCount,
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function usersList(UsersRepository $usersRepository): Response
    {
        // Sadece admin rolüne sahip kullanıcılar erişebilir
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $usersRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_admin_user_edit')]
    public function editUser(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {
        // Sadece admin rolüne sahip kullanıcılar erişebilir
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createFormBuilder($user)
            ->add('name', null, [
                'label' => 'Ad',
            ])
            ->add('surname', null, [
                'label' => 'Soyad',
            ])
            ->add('email', null, [
                'label' => 'E-posta',
            ])
            ->add('roles', null, [
                'label' => 'Roller',
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'Kullanıcı' => 'ROLE_USER',
                    'Yönetici' => 'ROLE_ADMIN',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Kullanıcı bilgileri başarıyla güncellendi.');

            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/edit_user.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(Request $request, Users $user, EntityManagerInterface $entityManager): Response
    {
        // Sadece admin rolüne sahip kullanıcılar erişebilir
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Kullanıcı başarıyla silindi.');
        }

        return $this->redirectToRoute('app_admin_users');
    }
}
