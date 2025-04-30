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
    private ?bool $is_complated = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'workoutLogs')]
    private ?Users $user_id = null;

    #[ORM\ManyToOne(inversedBy: 'workoutLogs')]
    private ?TrainingProgram $training_program_id = null;

    /**
     * @var Collection<int, WorkoutLogDetails>
     */
    #[ORM\OneToMany(targetEntity: WorkoutLogDetails::class, mappedBy: 'log_id')]
    private Collection $workoutLogDetails;

    public function __construct()
    {
        $this->workoutLogDetails = new ArrayCollection();
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

    public function isComplated(): ?bool
    {
        return $this->is_complated;
    }

    public function setIsComplated(bool $is_complated): static
    {
        $this->is_complated = $is_complated;

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

    public function getUserId(): ?Users
    {
        return $this->user_id;
    }

    public function setUserId(?Users $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getTrainingProgramId(): ?TrainingProgram
    {
        return $this->training_program_id;
    }

    public function setTrainingProgramId(?TrainingProgram $training_program_id): static
    {
        $this->training_program_id = $training_program_id;

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
            $workoutLogDetail->setLogId($this);
        }

        return $this;
    }

    public function removeWorkoutLogDetail(WorkoutLogDetails $workoutLogDetail): static
    {
        if ($this->workoutLogDetails->removeElement($workoutLogDetail)) {
            // set the owning side to null (unless already changed)
            if ($workoutLogDetail->getLogId() === $this) {
                $workoutLogDetail->setLogId(null);
            }
        }

        return $this;
    }
}
