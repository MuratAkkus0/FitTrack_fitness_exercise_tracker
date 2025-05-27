<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\NotBlank(message: 'E-posta adresi boş olamaz')]
    #[Assert\Email(message: 'Geçerli bir e-posta adresi giriniz')]
    #[Assert\Length(max: 180, maxMessage: 'E-posta adresi {{ limit }} karakterden uzun olamaz')]
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
    #[Assert\NotBlank(message: 'Ad boş olamaz')]
    #[Assert\Length(min: 2, max: 45, minMessage: 'Ad en az {{ limit }} karakter olmalıdır', maxMessage: 'Ad {{ limit }} karakterden uzun olamaz')]
    private ?string $name = null;

    #[ORM\Column(length: 45)]
    #[Assert\NotBlank(message: 'Soyad boş olamaz')]
    #[Assert\Length(min: 2, max: 45, minMessage: 'Soyad en az {{ limit }} karakter olmalıdır', maxMessage: 'Soyad {{ limit }} karakterden uzun olamaz')]
    private ?string $surname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profile_image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resetTokenExpiresAt = null;

    /**
     * @var Collection<int, TrainingProgram>
     */
    #[ORM\OneToMany(targetEntity: TrainingProgram::class, mappedBy: 'users')]
    private Collection $training_programs;

    /**
     * @var Collection<int, WorkoutLogs>
     */
    #[ORM\OneToMany(targetEntity: WorkoutLogs::class, mappedBy: 'user')]
    private Collection $workoutLogs;

    /**
     * @var Collection<int, FitnessGoal>
     */
    #[ORM\OneToMany(targetEntity: FitnessGoal::class, mappedBy: 'user')]
    private Collection $fitnessGoals;

    /**
     * @var Collection<int, BlogPost>
     */
    #[ORM\OneToMany(targetEntity: BlogPost::class, mappedBy: 'user')]
    private Collection $blogPosts;


    public function __construct()
    {
        $this->training_programs = new ArrayCollection();
        $this->workoutLogs = new ArrayCollection();
        $this->fitnessGoals = new ArrayCollection();
        $this->blogPosts = new ArrayCollection();
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

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getResetTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?\DateTimeImmutable $resetTokenExpiresAt): static
    {
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;

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
            $workoutLog->setUser($this);
        }

        return $this;
    }

    public function removeWorkoutLog(WorkoutLogs $workoutLog): static
    {
        if ($this->workoutLogs->removeElement($workoutLog)) {
            // set the owning side to null (unless already changed)
            if ($workoutLog->getUser() === $this) {
                $workoutLog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FitnessGoal>
     */
    public function getFitnessGoals(): Collection
    {
        return $this->fitnessGoals;
    }

    public function addFitnessGoal(FitnessGoal $fitnessGoal): static
    {
        if (!$this->fitnessGoals->contains($fitnessGoal)) {
            $this->fitnessGoals->add($fitnessGoal);
            $fitnessGoal->setUser($this);
        }

        return $this;
    }

    public function removeFitnessGoal(FitnessGoal $fitnessGoal): static
    {
        if ($this->fitnessGoals->removeElement($fitnessGoal)) {
            // set the owning side to null (unless already changed)
            if ($fitnessGoal->getUser() === $this) {
                $fitnessGoal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BlogPost>
     */
    public function getBlogPosts(): Collection
    {
        return $this->blogPosts;
    }

    public function addBlogPost(BlogPost $blogPost): static
    {
        if (!$this->blogPosts->contains($blogPost)) {
            $this->blogPosts->add($blogPost);
            $blogPost->setUser($this);
        }

        return $this;
    }

    public function removeBlogPost(BlogPost $blogPost): static
    {
        if ($this->blogPosts->removeElement($blogPost)) {
            // set the owning side to null (unless already changed)
            if ($blogPost->getUser() === $this) {
                $blogPost->setUser(null);
            }
        }

        return $this;
    }
}
