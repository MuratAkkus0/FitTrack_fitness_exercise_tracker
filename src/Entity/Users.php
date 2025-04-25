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
     * @var Collection<int, WorkoutLog>
     */
    #[ORM\OneToMany(targetEntity: WorkoutLog::class, mappedBy: 'user_id')]
    private Collection $program_id;

    /**
     * @var Collection<int, BlogPost>
     */
    #[ORM\OneToMany(targetEntity: BlogPost::class, mappedBy: 'user_id')]
    private Collection $blog_posts;

    public function __construct()
    {
        $this->training_program_id = new ArrayCollection();
        $this->program_id = new ArrayCollection();
        $this->blog_posts = new ArrayCollection();
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
    public function getTrainingProgramId(): Collection
    {
        return $this->training_program_id;
    }

    public function addTrainingProgramId(TrainingProgram $trainingProgramId): static
    {
        if (!$this->training_program_id->contains($trainingProgramId)) {
            $this->training_program_id->add($trainingProgramId);
            $trainingProgramId->setId($this);
        }

        return $this;
    }

    public function removeTrainingProgramId(TrainingProgram $trainingProgramId): static
    {
        if ($this->training_program_id->removeElement($trainingProgramId)) {
            // set the owning side to null (unless already changed)
            if ($trainingProgramId->getId() === $this) {
                $trainingProgramId->setId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WorkoutLog>
     */
    public function getProgramId(): Collection
    {
        return $this->program_id;
    }

    public function addProgramId(WorkoutLog $programId): static
    {
        if (!$this->program_id->contains($programId)) {
            $this->program_id->add($programId);
            $programId->setUserId($this);
        }

        return $this;
    }

    public function removeProgramId(WorkoutLog $workoutLogId): static
    {
        if ($this->program_id->removeElement($workoutLogId)) {
            // set the owning side to null (unless already changed)
            if ($workoutLogId->getUserId() === $this) {
                $workoutLogId->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BlogPost>
     */
    public function getBlogPosts(): Collection
    {
        return $this->blog_posts;
    }

    public function addBlogPost(BlogPost $blogPost): static
    {
        if (!$this->blog_posts->contains($blogPost)) {
            $this->blog_posts->add($blogPost);
            $blogPost->setUserId($this);
        }

        return $this;
    }

    public function removeBlogPost(BlogPost $blogPost): static
    {
        if ($this->blog_posts->removeElement($blogPost)) {
            // set the owning side to null (unless already changed)
            if ($blogPost->getUserId() === $this) {
                $blogPost->setUserId(null);
            }
        }

        return $this;
    }
}
