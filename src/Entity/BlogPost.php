<?php

namespace App\Entity;

use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
class BlogPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'blog_posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user_id = null;

    /**
     * @var Collection<int, WorkoutLog>
     */
    #[ORM\ManyToMany(targetEntity: WorkoutLog::class, inversedBy: 'blog_posts')]
    private Collection $workout_log_id;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $cerated_at = null;

    #[ORM\Column]
    private ?bool $is_public = null;

    public function __construct()
    {
        $this->workout_log_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUserId(): ?Users
    {
        return $this->user_id;
    }

    public function setUserId(?Users $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return Collection<int, WorkoutLog>
     */
    public function getWorkoutLogId(): Collection
    {
        return $this->workout_log_id;
    }

    public function addWorkoutLogId(WorkoutLog $workoutLogId): static
    {
        if (!$this->workout_log_id->contains($workoutLogId)) {
            $this->workout_log_id->add($workoutLogId);
        }

        return $this;
    }

    public function removeWorkoutLogId(WorkoutLog $workoutLogId): static
    {
        $this->workout_log_id->removeElement($workoutLogId);

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCeratedAt(): ?\DateTimeImmutable
    {
        return $this->cerated_at;
    }

    public function setCeratedAt(\DateTimeImmutable $cerated_at): static
    {
        $this->cerated_at = $cerated_at;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->is_public;
    }

    public function setIsPublic(bool $is_public): static
    {
        $this->is_public = $is_public;

        return $this;
    }
}
