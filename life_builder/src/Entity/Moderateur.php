<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Moderateur extends Utilisateur 
{   
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $category = null;
   

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }
}
