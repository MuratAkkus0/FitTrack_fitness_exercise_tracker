<?php

namespace App\Entity;

use App\Repository\WorkoutLogsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkoutLogsRepository::class)]
class WorkoutLogs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    private ?string $duration = null;

    #[ORM\Column]
    private ?bool $is_completed = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(nullable: true)]
    private ?int $estimated_calories = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $total_volume = null;

    #[ORM\Column(nullable: true)]
    private ?int $total_reps = null;

    #[ORM\ManyToOne(inversedBy: 'workoutLogs')]
    private ?Users $user = null;

    #[ORM\ManyToOne(inversedBy: 'workoutLogs')]
    private ?TrainingProgram $trainingProgram = null;

    /**
     * @var Collection<int, WorkoutLogDetails>
     */
    #[ORM\OneToMany(targetEntity: WorkoutLogDetails::class, mappedBy: 'workoutLog')]
    private Collection $workoutLogDetails;

    public function __construct()
    {
        $this->workoutLogDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

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

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function isCompleted(): ?bool
    {
        return $this->is_completed;
    }

    public function setIsCompleted(bool $is_completed): static
    {
        $this->is_completed = $is_completed;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
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

    public function getTrainingProgram(): ?TrainingProgram
    {
        return $this->trainingProgram;
    }

    public function setTrainingProgram(?TrainingProgram $trainingProgram): static
    {
        $this->trainingProgram = $trainingProgram;

        return $this;
    }

    /**
     * @return Collection<int, WorkoutLogDetails>
     */
    public function getWorkoutLogDetails(): Collection
    {
        return $this->workoutLogDetails;
    }

    public function addWorkoutLogDetail(WorkoutLogDetails $workoutLogDetail): static
    {
        if (!$this->workoutLogDetails->contains($workoutLogDetail)) {
            $this->workoutLogDetails->add($workoutLogDetail);
            $workoutLogDetail->setWorkoutLog($this);
        }

        return $this;
    }

    public function removeWorkoutLogDetail(WorkoutLogDetails $workoutLogDetail): static
    {
        if ($this->workoutLogDetails->removeElement($workoutLogDetail)) {
            // set the owning side to null (unless already changed)
            if ($workoutLogDetail->getWorkoutLog() === $this) {
                $workoutLogDetail->setWorkoutLog(null);
            }
        }

        return $this;
    }

    public function getEstimatedCalories(): ?int
    {
        return $this->estimated_calories;
    }

    public function setEstimatedCalories(?int $estimated_calories): static
    {
        $this->estimated_calories = $estimated_calories;

        return $this;
    }

    public function getTotalVolume(): ?string
    {
        return $this->total_volume;
    }

    public function setTotalVolume(?string $total_volume): static
    {
        $this->total_volume = $total_volume;

        return $this;
    }

    public function getTotalReps(): ?int
    {
        return $this->total_reps;
    }

    public function setTotalReps(?int $total_reps): static
    {
        $this->total_reps = $total_reps;

        return $this;
    }
}
