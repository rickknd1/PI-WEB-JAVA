<?php

namespace App\Entity;

use App\Repository\ChatRoomMembresRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatRoomMembresRepository::class)]
class ChatRoomMembres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chatRoomMembres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'chatRoomMembres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ChatRooms $chatRoom = null;

    public function getId(): ?int
    {
        return $this->id;
    }

public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getChatRoom(): ?ChatRooms
    {
        return $this->chatRoom;
    }

    public function setChatRoom(?ChatRooms $chatRoom): static
    {
        $this->chatRoom = $chatRoom;

        return $this;
    }
}
