<?php

namespace App\Entity;

use App\Repository\ShareRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShareRepository::class)]
class Share
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $create_at = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'share')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Post $post = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'sharedPosts')]
    private ?Post $sharedFrom = null;

    #[ORM\ManyToOne(inversedBy: 'share')]
    private ?User $user = null;

    public function getSharedFrom(): ?self
    {
        return $this->sharedFrom;
    }

    public function setSharedFrom(?self $post): self
    {
        $this->sharedFrom = $post;
        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(?\DateTimeImmutable $create_at): static
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
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
}
