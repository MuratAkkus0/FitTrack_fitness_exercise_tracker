<?php

namespace App\Entity;

use App\Repository\FitnessGoalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FitnessGoalRepository::class)]
class FitnessGoal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'fitnessGoals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $goal_type = null; // weight_loss, muscle_gain, strength, endurance, etc.

    #[ORM\Column(nullable: true)]
    private ?float $target_value = null;

    #[ORM\Column(nullable: true)]
    private ?float $current_value = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $unit = null; // kg, lbs, minutes, reps, etc.

    #[ORM\Column]
    private ?\DateTimeImmutable $start_date = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $target_date = null;

    #[ORM\Column]
    private ?bool $is_active = true;

    #[ORM\Column]
    private ?bool $is_completed = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completed_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->start_date = new \DateTimeImmutable();
        $this->is_active = true;
        $this->is_completed = false;
        $this->current_value = 0;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getGoalType(): ?string
    {
        return $this->goal_type;
    }

    public function setGoalType(string $goal_type): static
    {
        $this->goal_type = $goal_type;
        return $this;
    }

    public function getTargetValue(): ?float
    {
        return $this->target_value;
    }

    public function setTargetValue(?float $target_value): static
    {
        $this->target_value = $target_value;
        return $this;
    }

    public function getCurrentValue(): ?float
    {
        return $this->current_value;
    }

    public function setCurrentValue(?float $current_value): static
    {
        $this->current_value = $current_value;
        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): static
    {
        $this->unit = $unit;
        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeImmutable $start_date): static
    {
        $this->start_date = $start_date;
        return $this;
    }

    public function getTargetDate(): ?\DateTimeImmutable
    {
        return $this->target_date;
    }

    public function setTargetDate(?\DateTimeImmutable $target_date): static
    {
        $this->target_date = $target_date;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function isCompleted(): ?bool
    {
        return $this->is_completed;
    }

    public function setIsCompleted(bool $is_completed): static
    {
        $this->is_completed = $is_completed;

        if ($is_completed && !$this->completed_at) {
            $this->completed_at = new \DateTimeImmutable();
        }

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

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completed_at;
    }

    public function setCompletedAt(?\DateTimeImmutable $completed_at): static
    {
        $this->completed_at = $completed_at;
        return $this;
    }

    public function getProgressPercentage(): float
    {
        if (!$this->target_value || $this->target_value == 0) {
            return 0;
        }

        return min(100, ($this->current_value / $this->target_value) * 100);
    }

    public function getRemainingDays(): ?int
    {
        if (!$this->target_date) {
            return null;
        }

        $now = new \DateTimeImmutable();
        $diff = $this->target_date->diff($now);

        return $diff->invert ? $diff->days : -$diff->days;
    }
}
