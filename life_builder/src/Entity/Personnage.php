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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?array $imagesSecondaires = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $modifiedAt = null;

    /**
     * @var Collection<int, Signalement>
     */
    #[ORM\OneToMany(targetEntity: Signalement::class, mappedBy: 'personnage')]
    private Collection $signalements;

    #[ORM\Column(nullable: true)]
    private ?array $tags = null;

    #[ORM\Column(nullable: true)]
    private ?array $categories = null;

     #[ORM\Column()]
    private ?bool $isDeleted = false;



    
    public function __construct()
    {
        $this->histoires = new ArrayCollection();
        $this->apparences = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->persoLies = new ArrayCollection();
        $this->personnagesLies = new ArrayCollection();
        $this->signalements = new ArrayCollection();
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

    public function getImagesSecondaires(): ?array
    {
        return $this->imagesSecondaires;
    }

    public function setImagesSecondaires(?array $imagesSecondaires): static
    {
        $this->imagesSecondaires = $imagesSecondaires;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeImmutable $modifiedAt): static
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    /**
     * @return Collection<int, Signalement>
     */
    public function getSignalements(): Collection
    {
        return $this->signalements;
    }

    public function addSignalement(Signalement $signalement): static
    {
        if (!$this->signalements->contains($signalement)) {
            $this->signalements->add($signalement);
            $signalement->setPersonnage($this);
        }

        return $this;
    }

    public function removeSignalement(Signalement $signalement): static
    {
        if ($this->signalements->removeElement($signalement)) {
            // set the owning side to null (unless already changed)
            if ($signalement->getPersonnage() === $this) {
                $signalement->setPersonnage(null);
            }
        }

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function getCategories(): ?array
    {
        return $this->categories;
    }

    public function setCategories(?array $categories): static
    {
        $this->categories = $categories;

        return $this;
    }

    


    // Dans Entity/Personnage.php

public function getCategoriesHidden(): string
{
    // On transforme le tableau en chaîne pour le JS (ex: "Blob,Gris")
    return implode(',', $this->categories ?? []);
}

public function setCategoriesHidden(?string $categories): self
{
    // On transforme la chaîne du formulaire en tableau pour la BDD
    if ($categories) {
        $this->categories = explode(',', $categories);
    }
    return $this;
}

    

    /**
     * Get the value of isDeleted
     */ 
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set the value of isDeleted
     *
     * @return  self
     */ 
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
