<?php

namespace App\Entity;

use App\Repository\TrainingProgramRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrainingProgramRepository::class)]
class TrainingProgram
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 45)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'training_programs')]
    private ?Users $users = null;

    /**
     * @var Collection<int, TrainingExercises>
     */
    #[ORM\ManyToMany(targetEntity: TrainingExercises::class, inversedBy: 'trainingPrograms')]
    private Collection $training_exercises;

    /**
     * @var Collection<int, WorkoutLogs>
     */
    #[ORM\OneToMany(targetEntity: WorkoutLogs::class, mappedBy: 'training_program_id')]
    private Collection $workoutLogs;

    public function __construct()
    {
        $this->training_exercises = new ArrayCollection();
        $this->workoutLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?Users $id): static
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

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): static
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return Collection<int, TrainingExercises>
     */
    public function getTrainingExercises(): Collection
    {
        return $this->training_exercises;
    }

    public function addTrainingExercise(TrainingExercises $trainingExercise): static
    {
        if (!$this->training_exercises->contains($trainingExercise)) {
            $this->training_exercises->add($trainingExercise);
        }

        return $this;
    }

    public function removeTrainingExercise(TrainingExercises $trainingExercise): static
    {
        $this->training_exercises->removeElement($trainingExercise);

        return $this;
    }

    /**
     * @return Collection<int, WorkoutLogs>
     */
    public function getWorkoutLogs(): Collection
    {
        return $this->workoutLogs;
    }

    public function addWorkoutLog(WorkoutLogs $workoutLog): static
    {
        if (!$this->workoutLogs->contains($workoutLog)) {
            $this->workoutLogs->add($workoutLog);
            $workoutLog->setTrainingProgramId($this);
        }

        return $this;
    }

    public function removeWorkoutLog(WorkoutLogs $workoutLog): static
    {
        if ($this->workoutLogs->removeElement($workoutLog)) {
            // set the owning side to null (unless already changed)
            if ($workoutLog->getTrainingProgramId() === $this) {
                $workoutLog->setTrainingProgramId(null);
            }
        }

        return $this;
    }
}
