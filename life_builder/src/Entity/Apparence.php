<?php

namespace App\Entity;

use App\Repository\ApparenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ApparenceRepository::class)]
class Apparence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ordreAffichage = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(nullable: true)]
    private ?array $images = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'apparences')]
    private ?Personnage $personnage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdreAffichage(): ?int
    {
        return $this->ordreAffichage;
    }

    public function setOrdreAffichage(int $ordreAffichage): static
    {
        $this->ordreAffichage = $ordreAffichage;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): static
    {
        $this->images = $images;

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

    public function getPersonnage(): ?Personnage
    {
        return $this->personnage;
    }

    public function setPersonnage(?Personnage $personnage): static
    {
        $this->personnage = $personnage;

        return $this;
    }

    #[ORM\PrePersist]
    public function incrementeOrdre(LifecycleEventArgs $args): void
    {
        // Si ordre déjà défini, on ne fait rien
        if ($this->ordreAffichage !== null) {
            return;
        }

        $em = $args->getObjectManager();
        $repo = $em->getRepository(Histoire::class);

        // Récupère le maximum de ordreAffichage
        $last = $repo->findBy([], ['ordreAffichage' => 'DESC'], 1);
        $max = !empty($last) ? $last[0]->getOrdreAffichage() : 0;

        $this->ordreAffichage = $max + 1;
    }
}
