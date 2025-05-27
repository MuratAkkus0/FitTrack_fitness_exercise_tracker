<?php

namespace App\Entity;

use App\Repository\FavoriteExerciseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteExerciseRepository::class)]
#[ORM\UniqueConstraint(name: 'user_exercise_unique', columns: ['user_id', 'exercise_id'])]
class FavoriteExercise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\ManyToOne(targetEntity: TrainingExercises::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?TrainingExercises $exercise = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getExercise(): ?TrainingExercises
    {
        return $this->exercise;
    }

    public function setExercise(?TrainingExercises $exercise): static
    {
        $this->exercise = $exercise;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
