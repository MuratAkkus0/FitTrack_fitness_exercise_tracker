<?php

namespace App\Entity;

use App\Repository\WorkoutLogDetailsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkoutLogDetailsRepository::class)]
class WorkoutLogDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Tekrar sayısı boş olamaz')]
    #[Assert\Range(min: 1, max: 1000, notInRangeMessage: 'Tekrar sayısı {{ min }} ile {{ max }} arasında olmalıdır')]
    private ?int $reps = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotBlank(message: 'Ağırlık boş olamaz')]
    #[Assert\Range(min: 0, max: 999.99, notInRangeMessage: 'Ağırlık {{ min }} ile {{ max }} kg arasında olmalıdır')]
    private ?string $weight = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Set sayısı boş olamaz')]
    #[Assert\Range(min: 1, max: 50, notInRangeMessage: 'Set sayısı {{ min }} ile {{ max }} arasında olmalıdır')]
    private ?int $sets = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'workoutLogDetails')]
    private ?WorkoutLogs $workoutLog = null;

    #[ORM\ManyToOne(inversedBy: 'workoutLogDetails')]
    private ?TrainingExercises $exercise = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
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

    public function getWorkoutLog(): ?WorkoutLogs
    {
        return $this->workoutLog;
    }

    public function setWorkoutLog(?WorkoutLogs $workoutLog): static
    {
        $this->workoutLog = $workoutLog;

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
}
