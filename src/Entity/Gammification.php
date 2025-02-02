<?php

namespace App\Entity;

use App\Repository\GammificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GammificationRepository::class)]
class Gammification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type_abonnement = null;

    #[ORM\Column(nullable: true)]
    private ?int $score_user = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeAbonnement(): ?string
    {
        return $this->type_abonnement;
    }

    public function setTypeAbonnement(?string $type_abonnement): static
    {
        $this->type_abonnement = $type_abonnement;

        return $this;
    }

    public function getScoreUser(): ?int
    {
        return $this->score_user;
    }

    public function setScoreUser(?int $score_user): static
    {
        $this->score_user = $score_user;

        return $this;
    }

}
