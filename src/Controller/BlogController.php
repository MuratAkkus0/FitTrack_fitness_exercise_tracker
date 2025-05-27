<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Entity\WorkoutLogs;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/blog')]
#[IsGranted('ROLE_USER')]
class BlogController extends AbstractController
{
    #[Route('/', name: 'app_blog_index', methods: ['GET'])]
    public function index(BlogPostRepository $blogPostRepository): Response
    {
        // Get public blog posts for the main blog feed
        $publicPosts = $blogPostRepository->findBy(
            ['is_public' => true],
            ['created_at' => 'DESC'],
            20
        );

        return $this->render('user_dashboard/blog/index.html.twig', [
            'posts' => $publicPosts
        ]);
    }

    #[Route('/my-posts', name: 'app_blog_my_posts', methods: ['GET'])]
    public function myPosts(BlogPostRepository $blogPostRepository): Response
    {
        $user = $this->getUser();
        $myPosts = $blogPostRepository->findBy(
            ['user' => $user],
            ['created_at' => 'DESC']
        );

        return $this->render('user_dashboard/blog/my_posts.html.twig', [
            'posts' => $myPosts
        ]);
    }

    #[Route('/create', name: 'app_blog_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleCreatePost($request, $entityManager);
        }

        return $this->render('user_dashboard/blog/create.html.twig');
    }

    #[Route('/create-from-workout/{workoutId}', name: 'app_blog_create_from_workout', methods: ['GET', 'POST'])]
    public function createFromWorkout(int $workoutId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $workout = $entityManager->getRepository(WorkoutLogs::class)->find($workoutId);

        if (!$workout || $workout->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Workout not found or access denied.');
        }

        if ($request->isMethod('POST')) {
            return $this->handleCreatePost($request, $entityManager, $workout);
        }

        return $this->render('user_dashboard/blog/create_from_workout.html.twig', [
            'workout' => $workout
        ]);
    }

    #[Route('/post/{id}', name: 'app_blog_view', methods: ['GET'])]
    public function view(BlogPost $blogPost): Response
    {
        // Check if post is public or belongs to current user
        if (!$blogPost->isPublic() && $blogPost->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('This post is private.');
        }

        return $this->render('user_dashboard/blog/view.html.twig', [
            'post' => $blogPost
        ]);
    }

    #[Route('/post/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    public function edit(BlogPost $blogPost, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check if user owns this post
        if ($blogPost->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit your own posts.');
        }

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $blogPost->setTitle($data['title'] ?? $blogPost->getTitle());
            $blogPost->setContent($data['content'] ?? $blogPost->getContent());
            $blogPost->setIsPublic($data['is_public'] ?? $blogPost->isPublic());

            $entityManager->flush();

            return $this->json(['success' => true, 'message' => 'Post updated successfully']);
        }

        return $this->render('user_dashboard/blog/edit.html.twig', [
            'post' => $blogPost
        ]);
    }

    #[Route('/post/{id}/delete', name: 'app_blog_delete', methods: ['DELETE'])]
    public function delete(BlogPost $blogPost, EntityManagerInterface $entityManager): JsonResponse
    {
        // Check if user owns this post
        if ($blogPost->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only delete your own posts.');
        }

        $entityManager->remove($blogPost);
        $entityManager->flush();

        return $this->json(['success' => true, 'message' => 'Post deleted successfully']);
    }

    #[Route('/api/posts', name: 'app_blog_api_posts', methods: ['GET'])]
    public function apiPosts(Request $request, BlogPostRepository $blogPostRepository): JsonResponse
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);
        $userId = $request->query->get('user_id');

        $criteria = ['is_public' => true];
        if ($userId) {
            $criteria['user'] = $userId;
        }

        $posts = $blogPostRepository->findBy(
            $criteria,
            ['created_at' => 'DESC'],
            $limit,
            ($page - 1) * $limit
        );

        $postsData = array_map(function ($post) {
            return [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => substr($post->getContent(), 0, 200) . '...',
                'created_at' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
                'user' => [
                    'name' => $post->getUser()->getName() . ' ' . $post->getUser()->getSurname(),
                    'profile_image' => $post->getUser()->getProfileImage()
                ],
                'workout_log' => $post->getWorkoutLog() ? [
                    'id' => $post->getWorkoutLog()->getId(),
                    'duration' => $post->getWorkoutLog()->getDuration(),
                    'created_at' => $post->getWorkoutLog()->getCreatedAt()->format('Y-m-d')
                ] : null
            ];
        }, $posts);

        return $this->json([
            'posts' => $postsData,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    private function handleCreatePost(Request $request, EntityManagerInterface $entityManager, ?WorkoutLogs $workout = null): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $blogPost = new BlogPost();
            $blogPost->setTitle($data['title']);
            $blogPost->setContent($data['content']);
            $blogPost->setIsPublic($data['is_public'] ?? false);
            $blogPost->setUser($this->getUser());

            if ($workout) {
                $blogPost->setWorkoutLog($workout);
            }

            $entityManager->persist($blogPost);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Blog post created successfully',
                'post_id' => $blogPost->getId()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error creating blog post: ' . $e->getMessage()
            ], 500);
        }
    }
}
