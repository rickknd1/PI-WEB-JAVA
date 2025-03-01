<?php

namespace App\Entity;

use App\Repository\ChatRoomsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatRoomsRepository::class)]
class ChatRooms
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $cover = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Community::class, inversedBy: "chatRooms")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Community $community = null;

    /**
     * @var Collection<int, ChatRoomMembres>
     */
    #[ORM\OneToMany(targetEntity: ChatRoomMembres::class, mappedBy: 'chatRoom', cascade: ['remove'])]
    private Collection $chatRoomMembres;

    /**
     * @var Collection<int, Messages>
     */
    #[ORM\OneToMany(targetEntity: Messages::class, mappedBy: 'chatRoom', orphanRemoval: true, cascade: ['remove'])]
    private Collection $messages;

    public function __construct()
    {
        $this->chatRoomMembres = new ArrayCollection();
        $this->messages = new ArrayCollection();
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

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(string $cover): static
    {
        $this->cover = $cover;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    public function setCommunity(?Community $community): self
    {
        $this->community = $community;
        return $this;
    }

    /**
     * @return Collection<int, ChatRoomMembres>
     */
    public function getChatRoomMembres(): Collection
    {
        return $this->chatRoomMembres;
    }

    public function addChatRoomMembre(ChatRoomMembres $chatRoomMembre): static
    {
        if (!$this->chatRoomMembres->contains($chatRoomMembre)) {
            $this->chatRoomMembres->add($chatRoomMembre);
            $chatRoomMembre->setChatRoom($this);
        }

        return $this;
    }

    public function removeChatRoomMembre(ChatRoomMembres $chatRoomMembre): static
    {
        if ($this->chatRoomMembres->removeElement($chatRoomMembre)) {
            // set the owning side to null (unless already changed)
            if ($chatRoomMembre->getChatRoom() === $this) {
                $chatRoomMembre->setChatRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Messages>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Messages $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setChatRoom($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChatRoom() === $this) {
                $message->setChatRoom(null);
            }
        }

        return $this;
    }
}
