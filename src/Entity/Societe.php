<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocieteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;

#[ORM\Entity(repositoryClass: SocieteRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['societe:read']],
    denormalizationContext: ['groups' => ['societe:write']]
)]
#[UniqueEntity(
    fields: ['nom'],
    message: 'Une société avec ce nom existe déjà'
)]
class Societe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['societe:read', 'frais:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Le nom de la société est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Groups(['societe:read', 'societe:write', 'frais:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    #[Groups(['societe:read', 'societe:write'])]
    private ?string $adresse = null;

    #[ORM\Column(length: 14, nullable: true)]
    #[Assert\Length(
        exactly: 14,
        exactMessage: "Le SIRET doit contenir exactement {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[0-9]{14}$/",
        message: "Le SIRET doit contenir exactement 14 chiffres"
    )]
    #[Groups(['societe:read', 'societe:write'])]
    private ?string $siret = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas un email valide")]
    #[Groups(['societe:read', 'societe:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Regex(
        pattern: "/^[0-9]{10}$/",
        message: "Le numéro de téléphone doit contenir 10 chiffres"
    )]
    #[Groups(['societe:read', 'societe:write'])]
    private ?string $telephone = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'societes')]
    #[Groups(['societe:read'])]
    private Collection $commerciaux;

    #[ORM\OneToMany(mappedBy: 'societe', targetEntity: Frais::class)]
    private Collection $frais;

    public function __construct()
    {
        $this->commerciaux = new ArrayCollection();
        $this->frais = new ArrayCollection();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): static
    {
        $this->siret = $siret;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getCommerciaux(): Collection
    {
        return $this->commerciaux;
    }

    public function addCommercial(User $commercial): static
    {
        if (!$this->commerciaux->contains($commercial)) {
            $this->commerciaux->add($commercial);
            $commercial->addSociete($this);
        }
        return $this;
    }

    public function removeCommercial(User $commercial): static
    {
        if ($this->commerciaux->removeElement($commercial)) {
            $commercial->removeSociete($this);
        }
        return $this;
    }

    public function getFrais(): Collection
    {
        return $this->frais;
    }

    public function addFrais(Frais $frais): static
    {
        if (!$this->frais->contains($frais)) {
            $this->frais->add($frais);
            $frais->setSociete($this);
        }
        return $this;
    }

    public function removeFrais(Frais $frais): static
    {
        if ($this->frais->removeElement($frais)) {
            if ($frais->getSociete() === $this) {
                $frais->setSociete(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}
