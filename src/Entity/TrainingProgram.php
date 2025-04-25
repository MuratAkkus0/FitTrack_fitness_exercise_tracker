<?php

namespace App\Entity;

use App\Repository\TrainingProgramRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrainingProgramRepository::class)]
class TrainingProgram
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?Users $id): static
    {
        $this->id = $id;

        return $this;
    }
}
