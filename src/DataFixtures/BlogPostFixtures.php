<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Users;
use App\Entity\WorkoutLogs;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BlogPostFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Get users and workout logs from the database
        $users = $manager->getRepository(Users::class)->findAll();
        $workoutLogs = $manager->getRepository(WorkoutLogs::class)->findAll();

        if (empty($users)) {
            return; // No users to create posts for
        }

        // Sample blog posts
        $blogPosts = [
            [
                'title' => 'My First Month of Consistent Training',
                'content' => "I can't believe it's been a month since I started my fitness journey! 💪

When I first walked into the gym, I was intimidated by all the equipment and the people who seemed to know exactly what they were doing. But I decided to start small and be consistent.

Here's what I've learned:
• Consistency beats intensity - showing up every day matters more than perfect workouts
• Progressive overload is real - I can now lift 20% more than when I started
• Recovery is just as important as the workout itself
• The gym community is actually very supportive

My current routine:
- Monday: Upper body strength
- Tuesday: Cardio (30 min)
- Wednesday: Lower body strength
- Thursday: Rest or light yoga
- Friday: Full body circuit
- Weekend: Active recovery (hiking, walking)

The biggest change isn't just physical - I feel more confident, sleep better, and have more energy throughout the day. Looking forward to month two!

#fitness #beginner #consistency #progress",
                'is_public' => true,
                'workout_log' => null
            ],
            [
                'title' => 'Crushing My Personal Records Today!',
                'content' => "Today was one of those days where everything just clicked! 🔥

I've been working on my deadlift form for weeks, and today I finally hit a new PR. The feeling of lifting more weight than you ever thought possible is incredible.

What made the difference:
• Focused on proper form over heavy weight for weeks
• Consistent practice with lighter loads
• Better sleep and nutrition this week
• Mental preparation and visualization

The workout was intense but so rewarding. I'm already planning my next training cycle to keep this momentum going.

Remember: progress isn't always linear, but consistency pays off!

#deadlift #personalrecord #strength #training",
                'is_public' => true,
                'workout_log' => !empty($workoutLogs) ? $workoutLogs[0] : null
            ],
            [
                'title' => 'Why I Love Morning Workouts',
                'content' => "Switching to morning workouts was a game-changer for me! ☀️

I used to be a night owl who would drag myself to the gym after work, often skipping when I was tired or had other plans. But morning workouts have completely transformed my routine.

Benefits I've noticed:
• More energy throughout the day
• Better mood and mental clarity
• No excuses - it's done before the day gets busy
• The gym is less crowded
• Better sleep at night

Tips for becoming a morning person:
1. Go to bed earlier (obvious but crucial)
2. Prepare everything the night before
3. Start with just 2-3 days per week
4. Have a good pre-workout routine
5. Find an accountability partner

It took about 3 weeks to adjust, but now I can't imagine working out any other time. There's something special about starting your day with an accomplishment!

#morningworkout #routine #productivity #earlybird",
                'is_public' => true,
                'workout_log' => null
            ],
            [
                'title' => 'Leg Day Survival Guide',
                'content' => "Just finished another brutal leg day session! 🦵

For those who dread leg day (we've all been there), here are my tips for not just surviving but actually enjoying it:

Pre-workout prep:
• Good warm-up is crucial - 10 minutes minimum
• Proper hydration starting the day before
• Light meal 1-2 hours before training

During the workout:
• Focus on form over weight
• Take adequate rest between sets
• Listen to your body
• Don't skip the accessories

Post-workout recovery:
• Stretch immediately after
• Protein within 30 minutes
• Plenty of water
• Light walking helps with soreness

My current leg routine includes squats, Romanian deadlifts, Bulgarian split squats, and calf raises. The key is progressive overload while maintaining perfect form.

Tomorrow I'll probably walk like a robot, but it's so worth it! 😅

#legday #squats #strength #neverskiplegday",
                'is_public' => true,
                'workout_log' => !empty($workoutLogs) && count($workoutLogs) > 1 ? $workoutLogs[1] : null
            ],
            [
                'title' => 'My Nutrition Journey',
                'content' => "Sharing some thoughts on nutrition and how it's impacted my fitness journey 🥗

I used to think exercise was 90% of the equation, but I've learned that nutrition plays a huge role in how I feel and perform.

What's working for me:
• Meal prep on Sundays
• Eating protein with every meal
• Staying hydrated (3+ liters per day)
• Not restricting foods completely
• Listening to my body's hunger cues

I'm not following any specific diet - just trying to eat whole foods most of the time while still enjoying treats in moderation. The goal is sustainability, not perfection.

Some of my go-to meals:
- Breakfast: Oatmeal with berries and protein powder
- Lunch: Chicken salad with quinoa
- Dinner: Salmon with roasted vegetables
- Snacks: Greek yogurt, nuts, fruit

The biggest change has been my energy levels. I no longer have those afternoon crashes, and my workouts feel much stronger.

#nutrition #mealprep #balance #energy #wellness",
                'is_public' => false,
                'workout_log' => null
            ]
        ];

        foreach ($blogPosts as $index => $postData) {
            $blogPost = new BlogPost();
            $blogPost->setTitle($postData['title']);
            $blogPost->setContent($postData['content']);
            $blogPost->setIsPublic($postData['is_public']);

            // Assign to different users
            $userIndex = $index % count($users);
            $blogPost->setUser($users[$userIndex]);

            // Link to workout if specified
            if ($postData['workout_log']) {
                $blogPost->setWorkoutLog($postData['workout_log']);
            }

            // Set creation date to simulate posts over time
            $createdAt = new \DateTimeImmutable();
            $createdAt = $createdAt->modify('-' . (count($blogPosts) - $index) . ' days');
            $blogPost->setCreatedAt($createdAt);

            $manager->persist($blogPost);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            WorkoutLogsFixtures::class,
        ];
    }
}
