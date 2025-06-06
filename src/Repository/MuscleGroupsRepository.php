<?php

namespace App\Repository;

use App\Entity\MuscleGroups;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MuscleGroups>
 */
class MuscleGroupsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MuscleGroups::class);
    }
}
