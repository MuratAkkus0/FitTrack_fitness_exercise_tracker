<?php

namespace App\Entity;

use App\Repository\WorkoutLogDetailsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkoutLogDetailsRepository::class)]
class WorkoutLogDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $reps = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $weight = null;

    #[ORM\Column]
    private ?int $sets = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'workoutLogDetails')]
    private ?WorkoutLogs $log_id = null;

    #[ORM\ManyToOne(inversedBy: 'workoutLogDetails')]
    private ?TrainingExercises $exercise_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getReps(): ?int
    {
        return $this->reps;
    }

    public function setReps(int $reps): static
    {
        $this->reps = $reps;

        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(string $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getSets(): ?int
    {
        return $this->sets;
    }

    public function setSets(int $sets): static
    {
        $this->sets = $sets;

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

    public function getLogId(): ?WorkoutLogs
    {
        return $this->log_id;
    }

    public function setLogId(?WorkoutLogs $log_id): static
    {
        $this->log_id = $log_id;

        return $this;
    }

    public function getExerciseId(): ?TrainingExercises
    {
        return $this->exercise_id;
    }

    public function setExerciseId(?TrainingExercises $exercise_id): static
    {
        $this->exercise_id = $exercise_id;

        return $this;
    }
}
