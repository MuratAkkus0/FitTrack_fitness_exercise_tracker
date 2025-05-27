<?php

namespace App\Repository;

use App\Entity\FitnessGoal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FitnessGoal>
 */
class FitnessGoalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FitnessGoal::class);
    }

    public function findActiveGoalsByUser($user): array
    {
        return $this->createQueryBuilder('fg')
            ->where('fg.user = :user')
            ->andWhere('fg.is_active = true')
            ->andWhere('fg.is_completed = false')
            ->setParameter('user', $user)
            ->orderBy('fg.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCompletedGoalsByUser($user): array
    {
        return $this->createQueryBuilder('fg')
            ->where('fg.user = :user')
            ->andWhere('fg.is_completed = true')
            ->setParameter('user', $user)
            ->orderBy('fg.completed_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findGoalsByType($user, string $goalType): array
    {
        return $this->createQueryBuilder('fg')
            ->where('fg.user = :user')
            ->andWhere('fg.goal_type = :goalType')
            ->setParameter('user', $user)
            ->setParameter('goalType', $goalType)
            ->orderBy('fg.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
