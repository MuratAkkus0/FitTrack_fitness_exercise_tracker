<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\File;

#[Route('/dashboard/profile')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile')]
    public function index(): Response
    {
        // Kullanıcının giriş yapmış olması gerekiyor
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Users $user */
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Kullanıcının giriş yapmış olması gerekiyor
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var Users $user */
        $user = $this->getUser();

        $form = $this->createFormBuilder($user)
            ->add('name', TextType::class, [
                'label' => 'Ad',
                'attr' => [
                    'class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500'
                ],
            ])
            ->add('surname', TextType::class, [
                'label' => 'Soyad',
                'attr' => [
                    'class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500'
                ],
            ])
            ->add('profileImage', FileType::class, [
                'label' => 'Profil Fotoğrafı',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Lütfen geçerli bir resim dosyası yükleyin (JPEG, PNG)',
                    ])
                ],
                'attr' => [
                    'class' => 'block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500'
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Kaydet',
                'attr' => [
                    'class' => 'w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500'
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profileImageFile = $form->get('profileImage')->getData();

            if ($profileImageFile) {
                $originalFilename = pathinfo($profileImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profileImageFile->guessExtension();

                try {
                    $profileImageFile->move(
                        $this->getParameter('profile_images_directory'),
                        $newFilename
                    );

                    // Eski profil resmini silme (opsiyonel)
                    $oldProfileImage = $user->getProfileImage();
                    if ($oldProfileImage) {
                        $oldProfileImagePath = $this->getParameter('profile_images_directory') . '/' . $oldProfileImage;
                        if (file_exists($oldProfileImagePath)) {
                            unlink($oldProfileImagePath);
                        }
                    }

                    $user->setProfileImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Profil fotoğrafı yüklenirken bir hata oluştu.');
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profil bilgileriniz başarıyla güncellendi.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
