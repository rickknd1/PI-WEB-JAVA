<?php

namespace App\Entity;

use App\Repository\CommunityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommunityRepository::class)]
class Community
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $cover = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'communities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categories $id_categorie = null;

    /**
     * @var Collection<int, Events>
     */
    #[ORM\OneToMany(targetEntity: Events::class, mappedBy: 'id_community', cascade: ["remove"], orphanRemoval: true)]
    private Collection $events;

    /**
     * @var Collection<int, ChatRooms>
     */
    #[ORM\OneToMany(targetEntity: ChatRooms::class, mappedBy: 'community', cascade: ["remove"], orphanRemoval: true)]
    private Collection $chatRooms;

    #[ORM\Column]
    private ?int $nbr_membre = null;

    #[ORM\OneToMany(targetEntity: MembreComunity::class, mappedBy: "community", cascade: ["remove"], orphanRemoval: true)]
    private Collection $membreComunities;

    #[ORM\Column(type: 'boolean')]
    private ?bool $statut = true;

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->chatRooms = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(string $cover): static
    {
        $this->cover = $cover;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getIdCategorie(): ?Categories
    {
        return $this->id_categorie;
    }

    public function setIdCategorie(?Categories $id_categorie): static
    {
        $this->id_categorie = $id_categorie;

        return $this;
    }

    /**
     * @return Collection<int, Events>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }
    public function getChatrooms(): Collection
    {
        return $this->chatRooms;
    }

    public function addEvent(Events $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setIdCommunity($this);
        }

        return $this;
    }
    public function addChatrooms(ChatRooms $chatRooms): static
    {
        if (!$this->chatRooms->contains($chatRooms)) {
            $this->chatRooms->add($chatRooms);
            $chatRooms->setCommunity($this);
        }

        return $this;
    }

    public function removeEvent(Events $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getIdCommunity() === $this) {
                $event->setIdCommunity(null);
            }
        }

        return $this;
    }
    public function removeChatrooms(ChatRooms $chatRooms): static
    {
        if ($this->events->removeElement($chatRooms)) {
            // set the owning side to null (unless already changed)
            if ($chatRooms->getCommunity() === $this) {
                $chatRooms->setCommunity(null);
            }
        }

        return $this;
    }

    public function getNbrMembre(): ?int
    {
        return $this->nbr_membre;
    }

    public function setNbrMembre(int $nbr_membre): static
    {
        $this->nbr_membre = $nbr_membre;

        return $this;
    }
}
