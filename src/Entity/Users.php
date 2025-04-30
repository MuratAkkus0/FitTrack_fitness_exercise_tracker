<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25)]
    private ?string $name = null;

    #[ORM\Column(length: 25)]
    private ?string $surname = null;

    #[ORM\Column(length: 40)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $pass = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profile_image = null;

    /**
     * @var Collection<int, TrainingProgram>
     */
    #[ORM\OneToMany(targetEntity: TrainingProgram::class, mappedBy: 'id')]
    private Collection $training_program_id;

    /**
     * @var Collection<int, TrainingProgram>
     */
    #[ORM\OneToMany(targetEntity: TrainingProgram::class, mappedBy: 'users')]
    private Collection $training_programs;

    /**
     * @var Collection<int, WorkoutLogs>
     */
    #[ORM\OneToMany(targetEntity: WorkoutLogs::class, mappedBy: 'user_id')]
    private Collection $workoutLogs;


    public function __construct()
    {
        $this->training_program_id = new ArrayCollection();
        $this->training_programs = new ArrayCollection();
        $this->workoutLogs = new ArrayCollection();
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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setPass(string $pass): static
    {
        $this->pass = $pass;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getProfileImage(): ?string
    {
        return $this->profile_image;
    }

    public function setProfileImage(?string $profile_image): static
    {
        $this->profile_image = $profile_image;

        return $this;
    }


    /**
     * @return Collection<int, TrainingProgram>
     */
    public function getTrainingPrograms(): Collection
    {
        return $this->training_programs;
    }

    public function addTrainingProgram(TrainingProgram $trainingProgram): static
    {
        if (!$this->training_programs->contains($trainingProgram)) {
            $this->training_programs->add($trainingProgram);
            $trainingProgram->setUsers($this);
        }

        return $this;
    }

    public function removeTrainingProgram(TrainingProgram $trainingProgram): static
    {
        if ($this->training_programs->removeElement($trainingProgram)) {
            // set the owning side to null (unless already changed)
            if ($trainingProgram->getUsers() === $this) {
                $trainingProgram->setUsers(null);
            }
        }

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
            $workoutLog->setUserId($this);
        }

        return $this;
    }

    public function removeWorkoutLog(WorkoutLogs $workoutLog): static
    {
        if ($this->workoutLogs->removeElement($workoutLog)) {
            // set the owning side to null (unless already changed)
            if ($workoutLog->getUserId() === $this) {
                $workoutLog->setUserId(null);
            }
        }

        return $this;
    }

    
}
