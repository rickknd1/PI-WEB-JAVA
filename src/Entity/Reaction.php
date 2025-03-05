<?php

namespace App\Entity;

use App\Entity\Comment;
use App\Repository\ReactionRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\ReactionChoise;

#[ORM\Entity(repositoryClass: ReactionRepository::class)]
class Reaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, enumType: ReactionChoise::class)]
    private ReactionChoise $type;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'reactions')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Post $post = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'Comment')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Comment $comment = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ReactionChoise
    {
        return $this->type;
    }

    public function setType(ReactionChoise $type): self
    {
        $this->type = $type;
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

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): static
    {
        $this->comment = $comment;

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
