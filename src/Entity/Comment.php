<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le commentaire ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 1000,
        minMessage: "Le commentaire doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le commentaire ne peut pas dépasser {{ limit }} caractères."
    )]
    #[ORM\Column(type: "text")]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'Comment')]
    private ?Post $post = null;

    /**
     * @var Collection<int, Reaction>
     */
    #[ORM\OneToMany(targetEntity: Reaction::class, mappedBy: 'comment')]
    private Collection $reactions;

    public function __construct()
    {
        $this->reactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

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

    /**
     * @return Collection<int, Reaction>
     */
    public function getR(): Collection
    {
        return $this->r;
    }

    public function addR(Reaction $r): static
    {
        if (!$this->r->contains($r)) {
            $this->r->add($r);
            $r->setComment($this);
        }

        return $this;
    }

    public function removeR(Reaction $r): static
    {
        if ($this->r->removeElement($r)) {
            // set the owning side to null (unless already changed)
            if ($r->getComment() === $this) {
                $r->setComment(null);
            }
        }

        return $this;
    }
}
