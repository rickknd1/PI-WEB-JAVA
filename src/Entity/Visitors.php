<?php

namespace App\Entity;

use App\Repository\VisitorsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitorsRepository::class)]
class Visitors
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $nbrVisitors = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbrVisitors(): ?int
    {
        return $this->nbrVisitors;
    }

    public function setNbrVisitors(int $nbrVisitors): static
    {
        $this->nbrVisitors = $nbrVisitors;

        return $this;
    }
}
