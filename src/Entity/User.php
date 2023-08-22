<?php

namespace App\Entity;



use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getUser'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUser'])]
    #[Assert\NotBlank(message: "Veuillez renseigner un prénom", allowNull: false, normalizer: 'trim')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le prénom doit contenir {{ limit }} caractères minimum",
        maxMessage: "Le prénom ne peut pas faire plus de  {{ limit }} caractères maximum"
    )]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUser'])]
    #[Assert\NotBlank(message: "Veuillez renseigner un nom", allowNull: false, normalizer: 'trim')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom doit contenir {{ limit }} caractères minimum",
        maxMessage: "Le nom ne peut pas faire plus de  {{ limit }} caractères maximum"
    )]
    private ?string $lastname = null;

    #[ORM\Column]
    #[Groups(['getUser'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(['getUser'])]
    private ?Client $client = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
