<?php



// src/Entity/Ville.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le nom de la ville est obligatoire.")]
    #[Assert\Type(type:"string", message:"Le nom doit être une chaîne de caractères.")]
    #[Assert\Length(min: 2,minMessage: "Le nom doit contenir au moins {{ limit }} caractères.")]
    private $nom;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: "La description de la ville est obligatoire.")]
    #[Assert\Length(min: 2, minMessage: "La description doit contenir au moins {{ limit }} caractères.")]
    private $description;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: "La position de la ville est obligatoire.")]
    #[Assert\Length(min: 10, minMessage: "La position doit contenir au moins {{ limit }} caractères.")]
    private $position;

    /**
     * @var Collection<int, LieuCulturels>
     */
    #[ORM\OneToMany(targetEntity: LieuCulturels::class, mappedBy: 'ville')]
    private Collection $lieux;

    public function __construct()
    {
        $this->lieux = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return Collection<int, LieuCulturels>
     */
    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    public function addLieux(LieuCulturels $lieux): static
    {
        if (!$this->lieux->contains($lieux)) {
            $this->lieux->add($lieux);
            $lieux->setVille($this);
        }

        return $this;
    }

    public function removeLieux(LieuCulturels $lieux): static
    {
        if ($this->lieux->removeElement($lieux)) {
            // set the owning side to null (unless already changed)
            if ($lieux->getVille() === $this) {
                $lieux->setVille(null);
            }
        }

        return $this;
    }
}