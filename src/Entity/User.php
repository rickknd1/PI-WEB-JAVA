<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cet email.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Veuillez entrer un email valide.")]
    private ?string $email = null;

    #[ORM\Column]
    #[Assert\Choice(
        choices: ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_USER'],
        message: "Le rôle sélectionné est invalide."

    )]
    private string $role = 'ROLE_USER'; // Par défaut, un utilisateur a le rôle ROLE_USER

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(
        min: 6,
        minMessage: "Le mot de passe doit contenir au moins 6 caractères."
    )]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est obligatoire.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le nom d'utilisateur doit contenir au moins 3 caractères."
    )]
    private ?string $username = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de naissance est requise.")]
    #[Assert\LessThan("-18 years", message: "Vous devez avoir au moins 18 ans.")]
    private ?\DateTimeInterface $dateOB = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(
        choices: ['homme', 'femme', 'autres'],
        message: "Le genre sélectionné est invalide."
    )]
    private ?string $gender = null;

    #[ORM\ManyToMany(targetEntity: Categories::class)]
    #[Assert\Count(
        min: 1,
        minMessage: "Vous devez sélectionner au moins une catégorie d'intérêt."
    )]
    private Collection $interests;

    /**
     * @var Collection<int, MembreComunity>
     */
    #[ORM\OneToMany(targetEntity: MembreComunity::class, mappedBy: 'id_user')]
    private Collection $membreComunities;

    /**
     * @var Collection<int, ChatRoomMembres>
     */
    #[ORM\OneToMany(targetEntity: ChatRoomMembres::class, mappedBy: 'user')]
    private Collection $chatRoomMembres;

    /**
     * @var Collection<int, Messages>
     */
    #[ORM\OneToMany(targetEntity: Messages::class, mappedBy: 'user')]
    private Collection $messages;

    public function __construct()
    {
        $this->interests = new ArrayCollection();
        $this->membreComunities = new ArrayCollection();
        $this->chatRoomMembres = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        $this->username = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        // Retourne un tableau contenant uniquement le rôle de l'utilisateur
        return [$this->role];
    }

    public function setRoles(string $role): static
    {
        $this->role = $role;
        return $this;
    }
    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void {}

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getDateOB(): ?\DateTimeInterface
    {
        return $this->dateOB;
    }

    public function setDateOB(\DateTimeInterface $dateOB): static
    {
        $this->dateOB = $dateOB;
        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;
        return $this;
    }

    public function getInterests(): Collection
    {
        return $this->interests;
    }

    public function addInterest(Categories $interest): static
    {
        if (!$this->interests->contains($interest)) {
            $this->interests->add($interest);
        }
        return $this;
    }

    public function removeInterest(Categories $interest): static
    {
        $this->interests->removeElement($interest);
        return $this;
    }

    /**
     * @return Collection<int, MembreComunity>
     */
    public function getMembreComunities(): Collection
    {
        return $this->membreComunities;
    }

    public function addMembreComunity(MembreComunity $membreComunity): static
    {
        if (!$this->membreComunities->contains($membreComunity)) {
            $this->membreComunities->add($membreComunity);
            $membreComunity->setIdUser($this);
        }

        return $this;
    }

    public function removeMembreComunity(MembreComunity $membreComunity): static
    {
        if ($this->membreComunities->removeElement($membreComunity)) {
            // set the owning side to null (unless already changed)
            if ($membreComunity->getIdUser() === $this) {
                $membreComunity->setIdUser(null);
            }
        }

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
            $chatRoomMembre->setUser($this);
        }

        return $this;
    }

    public function removeChatRoomMembre(ChatRoomMembres $chatRoomMembre): static
    {
        if ($this->chatRoomMembres->removeElement($chatRoomMembre)) {
            // set the owning side to null (unless already changed)
            if ($chatRoomMembre->getUser() === $this) {
                $chatRoomMembre->setUser(null);
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
            $message->setUser($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }

        return $this;
    }
}
