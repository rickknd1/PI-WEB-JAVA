<?php

namespace App\Entity;

use App\Repository\AbonnementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $premium = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $normal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPremium(): ?string
    {
        return $this->premium;
    }

    public function setPremium(?string $premium): static
    {
        $this->premium = $premium;

        return $this;
    }

    public function getNormal(): ?string
    {
        return $this->normal;
    }

    public function setNormal(?string $normal): static
    {
        $this->normal = $normal;

        return $this;
    }
}
