<?php

namespace App\Entity;

use App\Repository\ModStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModStatusRepository::class)]
class ModStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?\DateInterval $duree = null;

    #[ORM\Column]
    private ?int $nbSig = null;

    #[ORM\Column]
    private array $sanctions = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDuree(): ?\DateInterval
    {
        return $this->duree;
    }

    public function setDuree(\DateInterval $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getNbSig(): ?int
    {
        return $this->nbSig;
    }

    public function setNbSig(int $nbSig): static
    {
        $this->nbSig = $nbSig;

        return $this;
    }

    public function getSanctions(): array
    {
        return $this->sanctions;
    }

    public function setSanctions(array $sanctions): static
    {
        $this->sanctions = $sanctions;

        return $this;
    }
}
