<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController{
    #[Route("/")]
    public function index(){
        $blogPosts = [
            [
                'id' => 1,
                'title' => "Fitness Yolculuğuma Nasıl Başladım?",
                'content' => "3 ay önce spora başladığımda kendimi çok motive hissediyordum ama ilk haftalarda adapte olmak zor oldu. Şimdi haftada 4 gün düzenli antrenman yapıyorum ve sonuçları görmeye başladım!",
                'createdAt' => new \DateTime('2023-05-15'),
                'user' => [
                    'username' => 'fitnesstutkunu',
                    'avatar' => 'https://i.pravatar.cc/150?img=12'
                ],
                'image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
                'workoutLog' => [
                    'trainingProgram' => [
                        'name' => 'Başlangıç Programı'
                    ]
                ]
            ],
            [
                'id' => 2,
                'title' => "Bench Press Tekniğini Geliştirmek",
                'content' => "Doğru bench press formu için 5 kritik nokta: 1) Omuzlar sabit 2) Bel hafif kavis 3) Bilekler dik 4) Kontrollü indirme 5) Nefes kontrolü. Bu detaylara dikkat ederek 1 ayda max ağırlığımı %20 artırdım!",
                'createdAt' => new \DateTime('2023-06-02'),
                'user' => [
                    'username' => 'powerlifter42',
                    'avatar' => 'https://i.pravatar.cc/150?img=45'
                ],
                'image' => 'https://images.unsplash.com/photo-1534258936925-c58bed479fc3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
                'workoutLog' => [
                    'trainingProgram' => [
                        'name' => 'Güç Geliştirme'
                    ]
                ]
            ],
            [
                'id' => 3,
                'title' => "Yoga ile Esneklik Kazanın",
                'content' => "Fitness rutinime yoga ekledikten sonra vücudumdaki değişim inanılmaz! Her sabah 20 dakika yoga yaparak hem zihinsel odaklanmam arttı hem de antrenman performansım gelişti. İşte benim favori pozlarım...",
                'createdAt' => new \DateTime('2023-06-10'),
                'user' => [
                    'username' => 'yogasever',
                    'avatar' => 'https://i.pravatar.cc/150?img=31'
                ],
                'image' => 'https://images.unsplash.com/photo-1545205597-3d9d02c29597?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80',
                'workoutLog' => [
                    'trainingProgram' => [
                        'name' => 'Esneklik Programı'
                    ]
                ]
            ]
        ];
        return $this->render('Home.html.twig',[
            "blogPosts"=>$blogPosts
        ]);
    }
 
    
}