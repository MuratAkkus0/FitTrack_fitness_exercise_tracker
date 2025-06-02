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

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image_url = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $video_url = null;

    /**
     * @var Collection<int, TrainingProgram>
     */
    #[ORM\ManyToMany(targetEntity: TrainingProgram::class, mappedBy: 'training_exercises')]
    private Collection $trainingPrograms;

    /**
     * @var Collection<int, WorkoutLogDetails>
     */
    #[ORM\OneToMany(targetEntity: WorkoutLogDetails::class, mappedBy: 'exercise')]
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

    public function setId(int $id): static
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

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(?string $image_url): static
    {
        $this->image_url = $image_url;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->video_url;
    }

    public function setVideoUrl(?string $video_url): static
    {
        $this->video_url = $video_url;

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
            $workoutLogDetail->setExercise($this);
        }

        return $this;
    }

    public function removeWorkoutLogDetail(WorkoutLogDetails $workoutLogDetail): static
    {
        if ($this->workoutLogDetails->removeElement($workoutLogDetail)) {
            // set the owning side to null (unless already changed)
            if ($workoutLogDetail->getExercise() === $this) {
                $workoutLogDetail->setExercise(null);
            }
        }

        return $this;
    }
}
