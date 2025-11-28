<?php

namespace App\Entity;

use App\Repository\PersonnageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonnageRepository::class)]
class Personnage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, Histoire>
     */
    #[ORM\OneToMany(targetEntity: Histoire::class, mappedBy: 'personnage')]
    private Collection $histoires;

    /**
     * @var Collection<int, Apparence>
     */
    #[ORM\OneToMany(targetEntity: Apparence::class, mappedBy: 'personnage')]
    private Collection $apparences;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'personnage')]
    private Collection $commentaires;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'personnagesLies')]
    private Collection $persoLies;

    /**
     * @var Collection<int, self>
     */
    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'persoLies')]
    private Collection $personnagesLies;

    #[ORM\ManyToOne(inversedBy: 'personnages')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column]
    private ?bool $isPublic = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    
    public function __construct()
    {
        $this->histoires = new ArrayCollection();
        $this->apparences = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->persoLies = new ArrayCollection();
        $this->personnagesLies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Histoire>
     */
    public function getHistoires(): Collection
    {
        return $this->histoires;
    }

    public function addHistoire(Histoire $histoire): static
    {
        if (!$this->histoires->contains($histoire)) {
            $this->histoires->add($histoire);
            $histoire->setPersonnage($this);
        }

        return $this;
    }

    public function removeHistoire(Histoire $histoire): static
    {
        if ($this->histoires->removeElement($histoire)) {
            // set the owning side to null (unless already changed)
            if ($histoire->getPersonnage() === $this) {
                $histoire->setPersonnage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Apparence>
     */
    public function getApparences(): Collection
    {
        return $this->apparences;
    }

    public function addApparence(Apparence $apparence): static
    {
        if (!$this->apparences->contains($apparence)) {
            $this->apparences->add($apparence);
            $apparence->setPersonnage($this);
        }

        return $this;
    }

    public function removeApparence(Apparence $apparence): static
    {
        if ($this->apparences->removeElement($apparence)) {
            // set the owning side to null (unless already changed)
            if ($apparence->getPersonnage() === $this) {
                $apparence->setPersonnage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setPersonnage($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getPersonnage() === $this) {
                $commentaire->setPersonnage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getPersoLies(): Collection
    {
        return $this->persoLies;
    }

    public function addPersoLy(self $persoLy): static
    {
        if (!$this->persoLies->contains($persoLy)) {
            $this->persoLies->add($persoLy);
        }

        return $this;
    }

    public function removePersoLy(self $persoLy): static
    {
        $this->persoLies->removeElement($persoLy);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getPersonnagesLies(): Collection
    {
        return $this->personnagesLies;
    }

    public function addPersonnagesLy(self $personnagesLy): static
    {
        if (!$this->personnagesLies->contains($personnagesLy)) {
            $this->personnagesLies->add($personnagesLy);
            $personnagesLy->addPersoLy($this);
        }

        return $this;
    }

    public function removePersonnagesLy(self $personnagesLy): static
    {
        if ($this->personnagesLies->removeElement($personnagesLy)) {
            $personnagesLy->removePersoLy($this);
        }

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;

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


    
}
