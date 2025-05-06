<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 45)]
    private ?string $name = null;

    #[ORM\Column(length: 45)]
    private ?string $surname = null;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
