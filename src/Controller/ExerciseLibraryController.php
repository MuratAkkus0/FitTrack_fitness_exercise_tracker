<?php

namespace App\Controller;

use App\Entity\TrainingExercises;
use App\Entity\FavoriteExercise;
use App\Enum\MuscleGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/exercise-library')]
#[IsGranted('ROLE_USER')]
class ExerciseLibraryController extends AbstractController
{
    #[Route('/', name: 'app_exercises_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $muscleGroup = $request->query->get('muscle_group');
        $search = $request->query->get('search');

        $queryBuilder = $entityManager->getRepository(TrainingExercises::class)
            ->createQueryBuilder('e');

        if ($muscleGroup && $muscleGroup !== 'all') {
            $queryBuilder->andWhere('e.target_muscle_group = :muscleGroup')
                ->setParameter('muscleGroup', $muscleGroup);
        }

        if ($search) {
            $queryBuilder->andWhere('e.name LIKE :search OR e.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $exercises = $queryBuilder->orderBy('e.name', 'ASC')->getQuery()->getResult();

        // Kas gruplarını getir
        $muscleGroups = MuscleGroup::cases();

        // Kullanıcının favori egzersizlerini kontrol et
        $user = $this->getUser();
        $favoriteExerciseIds = [];
        if ($user) {
            $favoriteExercises = $entityManager->getRepository(FavoriteExercise::class)
                ->findBy(['user' => $user]);
            $favoriteExerciseIds = array_map(fn($fav) => $fav->getExercise()->getId(), $favoriteExercises);
        }

        return $this->render('user_dashboard/exercise_library/index.html.twig', [
            'exercises' => $exercises,
            'muscleGroups' => $muscleGroups,
            'currentMuscleGroup' => $muscleGroup,
            'currentSearch' => $search,
            'favoriteExerciseIds' => $favoriteExerciseIds
        ]);
    }

    #[Route('/api/exercises', name: 'app_exercises_api', methods: ['GET'])]
    public function getExercisesApi(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $muscleGroup = $request->query->get('muscle_group');
            $search = $request->query->get('search');
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 20);

            $queryBuilder = $entityManager->getRepository(TrainingExercises::class)
                ->createQueryBuilder('e');

            if ($muscleGroup && $muscleGroup !== 'all') {
                $queryBuilder->andWhere('e.target_muscle_group = :muscleGroup')
                    ->setParameter('muscleGroup', $muscleGroup);
            }

            if ($search) {
                $queryBuilder->andWhere('e.name LIKE :search OR e.description LIKE :search')
                    ->setParameter('search', '%' . $search . '%');
            }

            $totalQuery = clone $queryBuilder;
            $total = $totalQuery->select('COUNT(e.id)')->getQuery()->getSingleScalarResult();

            $exercises = $queryBuilder
                ->orderBy('e.name', 'ASC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $user = $this->getUser();
            $exerciseData = array_map(function ($exercise) use ($entityManager, $user) {
                $isFavorite = $entityManager->getRepository(FavoriteExercise::class)
                    ->findOneBy(['user' => $user, 'exercise' => $exercise]) !== null;

                return [
                    'id' => $exercise->getId(),
                    'name' => $exercise->getName(),
                    'description' => $exercise->getDescription(),
                    'muscleGroup' => $exercise->getTargetMuscleGroup()?->value,
                    'muscleGroupLabel' => $exercise->getTargetMuscleGroup()?->getLabel(),
                    'imageUrl' => $exercise->getImageUrl(),
                    'videoUrl' => $exercise->getVideoUrl(),
                    'instructions' => $exercise->getInstructions(),
                    'isFavorite' => $isFavorite
                ];
            }, $exercises);

            return $this->json([
                'success' => true,
                'exercises' => $exerciseData,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error loading exercises: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/exercise/{id}', name: 'app_exercises_show', methods: ['GET'])]
    public function exerciseDetail(int $id, EntityManagerInterface $entityManager): Response
    {
        $exercise = $entityManager->getRepository(TrainingExercises::class)->find($id);

        if (!$exercise) {
            throw $this->createNotFoundException('Egzersiz bulunamadı.');
        }

        // Kullanıcının bu egzersizle ilgili son performansını getir
        $user = $this->getUser();
        $recentPerformance = $entityManager->createQueryBuilder()
            ->select('wld.weight, wld.reps, wld.sets, wl.created_at')
            ->from('App\Entity\WorkoutLogDetails', 'wld')
            ->join('wld.workoutLog', 'wl')
            ->where('wl.user = :user')
            ->andWhere('wld.exercise = :exercise')
            ->andWhere('wl.is_completed = true')
            ->setParameter('user', $user)
            ->setParameter('exercise', $exercise)
            ->orderBy('wl.created_at', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Benzer egzersizleri getir (aynı kas grubu)
        $similarExercises = $entityManager->getRepository(TrainingExercises::class)
            ->createQueryBuilder('e')
            ->where('e.target_muscle_group = :muscleGroup')
            ->andWhere('e.id != :currentId')
            ->setParameter('muscleGroup', $exercise->getTargetMuscleGroup())
            ->setParameter('currentId', $exercise->getId())
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();

        // Kullanıcının bu egzersizi favorilere eklemiş mi kontrol et
        $isFavorite = $entityManager->getRepository(FavoriteExercise::class)
            ->findOneBy(['user' => $user, 'exercise' => $exercise]) !== null;

        return $this->render('user_dashboard/exercise_library/detail.html.twig', [
            'exercise' => $exercise,
            'recentPerformance' => $recentPerformance,
            'similarExercises' => $similarExercises,
            'isFavorite' => $isFavorite
        ]);
    }

    #[Route('/muscle-groups', name: 'app_exercises_muscle_groups_api', methods: ['GET'])]
    public function getMuscleGroups(): JsonResponse
    {
        $muscleGroups = array_map(function ($muscleGroup) {
            return [
                'value' => $muscleGroup->value,
                'label' => $muscleGroup->getLabel()
            ];
        }, MuscleGroup::cases());

        return $this->json($muscleGroups);
    }

    #[Route('/create-custom', name: 'app_exercises_create', methods: ['POST'])]
    public function createCustomExercise(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validasyon
            if (empty($data['name']) || empty($data['description']) || empty($data['muscleGroup'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Tüm alanlar doldurulmalıdır'
                ], 400);
            }

            // Kas grubu geçerli mi kontrol et
            $muscleGroup = null;
            foreach (MuscleGroup::cases() as $mg) {
                if ($mg->value === $data['muscleGroup']) {
                    $muscleGroup = $mg;
                    break;
                }
            }

            if (!$muscleGroup) {
                return $this->json([
                    'success' => false,
                    'message' => 'Geçersiz kas grubu'
                ], 400);
            }

            // Aynı isimde egzersiz var mı kontrol et
            $existingExercise = $entityManager->getRepository(TrainingExercises::class)
                ->findOneBy(['name' => $data['name']]);

            if ($existingExercise) {
                return $this->json([
                    'success' => false,
                    'message' => 'Bu isimde bir egzersiz zaten mevcut'
                ], 400);
            }

            // Yeni egzersiz oluştur
            $exercise = new TrainingExercises();
            $exercise->setName($data['name']);
            $exercise->setDescription($data['description']);
            $exercise->setTargetMuscleGroup($muscleGroup);

            // Opsiyonel alanları ekle
            if (!empty($data['imageUrl'])) {
                $exercise->setImageUrl($data['imageUrl']);
            }
            if (!empty($data['videoUrl'])) {
                $exercise->setVideoUrl($data['videoUrl']);
            }
            if (!empty($data['instructions'])) {
                $exercise->setInstructions($data['instructions']);
            }

            $entityManager->persist($exercise);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Özel egzersiz başarıyla oluşturuldu',
                'exercise' => [
                    'id' => $exercise->getId(),
                    'name' => $exercise->getName(),
                    'description' => $exercise->getDescription(),
                    'muscleGroup' => $exercise->getTargetMuscleGroup()->value
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Egzersiz oluşturulurken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/favorites', name: 'app_exercises_favorites', methods: ['GET'])]
    public function getFavorites(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // En çok kullanılan egzersizleri favori olarak kabul et
        $favoriteExercises = $entityManager->createQueryBuilder()
            ->select('e, COUNT(wld.id) as usage_count')
            ->from('App\Entity\TrainingExercises', 'e')
            ->join('e.workoutLogDetails', 'wld')
            ->join('wld.workoutLog', 'wl')
            ->where('wl.user = :user')
            ->andWhere('wl.is_completed = true')
            ->setParameter('user', $user)
            ->groupBy('e.id')
            ->orderBy('usage_count', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('user_dashboard/exercise_library/favorites.html.twig', [
            'favoriteExercises' => $favoriteExercises
        ]);
    }

    #[Route('/toggle-favorite/{id}', name: 'app_exercises_toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->getUser();
            $exercise = $entityManager->getRepository(TrainingExercises::class)->find($id);

            if (!$exercise) {
                return $this->json([
                    'success' => false,
                    'message' => 'Egzersiz bulunamadı'
                ], 404);
            }

            // Mevcut favori kaydını kontrol et
            $existingFavorite = $entityManager->getRepository(FavoriteExercise::class)
                ->findOneBy(['user' => $user, 'exercise' => $exercise]);

            if ($existingFavorite) {
                // Favorilerden çıkar
                $entityManager->remove($existingFavorite);
                $isFavorite = false;
                $message = 'Egzersiz favorilerden çıkarıldı';
            } else {
                // Favorilere ekle
                $favorite = new FavoriteExercise();
                $favorite->setUser($user);
                $favorite->setExercise($exercise);
                $entityManager->persist($favorite);
                $isFavorite = true;
                $message = 'Egzersiz favorilere eklendi';
            }

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => $message,
                'isFavorite' => $isFavorite
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Favori durumu değiştirilirken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}
