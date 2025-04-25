<?php

namespace App\Entity;

use App\Enum\MuscleGroup as EnumMuscleGroup;
use App\Repository\TrainingExercisesRepository;
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

    public function __construct()
    {
      
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
}
