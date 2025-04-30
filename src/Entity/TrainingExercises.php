<?php

namespace App\Entity;

use App\Enum\MuscleGroup as EnumMuscleGroup;
use App\Repository\TrainingExercisesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrainingExercisesRepository::class)]
class TrainingExercises
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', enumType: EnumMuscleGroup::class)]
    private ?EnumMuscleGroup $target_muscle_group = null;

    /**
     * @var Collection<int, TrainingProgram>
     */
    #[ORM\ManyToMany(targetEntity: TrainingProgram::class, mappedBy: 'training_exercises')]
    private Collection $trainingPrograms;

    /**
     * @var Collection<int, WorkoutLogDetails>
     */
    #[ORM\OneToMany(targetEntity: WorkoutLogDetails::class, mappedBy: 'exercise_id')]
    private Collection $workoutLogDetails;

    public function __construct()
    {
        $this->trainingPrograms = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }



    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTargetMuscleGroup(): ?EnumMuscleGroup
    {
        return $this->target_muscle_group;
    }

    public function setTargetMuscleGroup(?EnumMuscleGroup $target_muscle_group): static
    {
        $this->target_muscle_group = $target_muscle_group;

        return $this;
    }

    /**
     * @return Collection<int, TrainingProgram>
     */
    public function getTrainingPrograms(): Collection
    {
        return $this->trainingPrograms;
    }

    public function addTrainingProgram(TrainingProgram $trainingProgram): static
    {
        if (!$this->trainingPrograms->contains($trainingProgram)) {
            $this->trainingPrograms->add($trainingProgram);
            $trainingProgram->addTrainingExercise($this);
        }

        return $this;
    }

    public function removeTrainingProgram(TrainingProgram $trainingProgram): static
    {
        if ($this->trainingPrograms->removeElement($trainingProgram)) {
            $trainingProgram->removeTrainingExercise($this);
        }

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
            $workoutLogDetail->setExerciseId($this);
        }

        return $this;
    }

    public function removeWorkoutLogDetail(WorkoutLogDetails $workoutLogDetail): static
    {
        if ($this->workoutLogDetails->removeElement($workoutLogDetail)) {
            // set the owning side to null (unless already changed)
            if ($workoutLogDetail->getExerciseId() === $this) {
                $workoutLogDetail->setExerciseId(null);
            }
        }

        return $this;
    }
}
