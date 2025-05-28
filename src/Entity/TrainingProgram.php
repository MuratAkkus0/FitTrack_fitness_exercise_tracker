<?php

namespace App\Entity;

use App\Repository\TrainingProgramRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrainingProgramRepository::class)]
class TrainingProgram
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 45)]
    #[Assert\NotBlank(message: 'Program name cannot be empty')]
    #[Assert\Length(min: 3, max: 45, minMessage: 'Program name must be at least {{ limit }} characters', maxMessage: 'Program name cannot be longer than {{ limit }} characters')]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 1, max: 7, notInRangeMessage: 'Weekly workout count must be between {{ min }} and {{ max }}')]
    private ?int $workouts_per_week = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 15, max: 300, notInRangeMessage: 'Workout duration must be between {{ min }} and {{ max }} minutes')]
    private ?int $duration_minutes = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $difficulty_level = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column]
    private ?bool $is_active = true;

    #[ORM\Column]
    private ?bool $is_public = false;

    #[ORM\Column(length: 10, nullable: true, unique: true)]
    private ?string $share_code = null;

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
    #[ORM\OneToMany(targetEntity: WorkoutLogs::class, mappedBy: 'trainingProgram')]
    private Collection $workoutLogs;

    public function __construct()
    {
        $this->training_exercises = new ArrayCollection();
        $this->workoutLogs = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->is_active = true;
        $this->is_public = false;
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

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getWorkoutsPerWeek(): ?int
    {
        return $this->workouts_per_week;
    }

    public function setWorkoutsPerWeek(?int $workouts_per_week): static
    {
        $this->workouts_per_week = $workouts_per_week;

        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->duration_minutes;
    }

    public function setDurationMinutes(?int $duration_minutes): static
    {
        $this->duration_minutes = $duration_minutes;

        return $this;
    }

    public function getDifficultyLevel(): ?string
    {
        return $this->difficulty_level;
    }

    public function setDifficultyLevel(?string $difficulty_level): static
    {
        $this->difficulty_level = $difficulty_level;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

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

    public function isPublic(): ?bool
    {
        return $this->is_public;
    }

    public function setIsPublic(bool $is_public): static
    {
        $this->is_public = $is_public;

        return $this;
    }

    public function getShareCode(): ?string
    {
        return $this->share_code;
    }

    public function setShareCode(?string $share_code): static
    {
        $this->share_code = $share_code;

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
            $workoutLog->setTrainingProgram($this);
        }

        return $this;
    }

    public function removeWorkoutLog(WorkoutLogs $workoutLog): static
    {
        if ($this->workoutLogs->removeElement($workoutLog)) {
            // set the owning side to null (unless already changed)
            if ($workoutLog->getTrainingProgram() === $this) {
                $workoutLog->setTrainingProgram(null);
            }
        }

        return $this;
    }
}
