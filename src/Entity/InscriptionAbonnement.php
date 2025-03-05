<?php

namespace App\Entity;

use App\Repository\InscriptionAbonnementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionAbonnementRepository::class)]
class InscriptionAbonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $user = null;

    #[ORM\ManyToOne(targetEntity: Abonnements::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'abonnement_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Abonnements $abonnement = null;


    #[ORM\Column]
    private ?\DateTimeImmutable $subscribed_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expired_at = null;

    #[ORM\Column(length: 255)]
    private ?string $mode_paiement = null;

    #[ORM\Column]
    private ?bool $renouvellement_auto = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(user $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAbonnement(): ?Abonnements
    {
        return $this->abonnement;
    }

    public function setAbonnement(?Abonnements $abonnement): static
    {
        $this->abonnement = $abonnement;

        return $this;
    }

    public function getSubscribedAt(): ?\DateTimeImmutable
    {
        return $this->subscribed_at;
    }

    public function setSubscribedAt(\DateTimeImmutable $subscribed_at): static
    {
        $this->subscribed_at = $subscribed_at;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeImmutable
    {
        return $this->expired_at;
    }

    public function setExpiredAt(\DateTimeImmutable $expired_at): static
    {
        $this->expired_at = $expired_at;

        return $this;
    }

    public function getModePaiement(): ?string
    {
        return $this->mode_paiement;
    }

    public function setModePaiement(string $mode_paiement): static
    {
        $this->mode_paiement = $mode_paiement;

        return $this;
    }

    public function isRenouvellementAuto(): ?bool
    {
        return $this->renouvellement_auto;
    }

    public function setRenouvellementAuto(bool $renouvellement_auto): static
    {
        $this->renouvellement_auto = $renouvellement_auto;

        return $this;
    }
}
