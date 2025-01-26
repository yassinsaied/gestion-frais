<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use App\Validator\SocieteUser;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FraisRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Provider\Frais\MesFraisProvider;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\State\Processor\Frais\FraisProcessor;


#[ORM\Entity(repositoryClass: FraisRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/frais',
            processor: FraisProcessor::class,
            status: 201,
            description: 'Crée une nouvelle note de frais',
            openapiContext: [
                'summary' => 'Crée une nouvelle note de frais',
                'responses' => [
                    '201' => [
                        'description' => 'Note de frais créée avec succès'
                    ],
                    '400' => [
                        'description' => 'Données invalides'
                    ],
                    '401' => [
                        'description' => 'Non autorisé'
                    ]
                ]
            ]
        ),
        // Item operations
        new Get(
            uriTemplate: '/frais/{id}',
            security: "object.getUser() == user",
            securityMessage: "Vous n'avez pas accès à ces frais"
        ),
        new Put(
            uriTemplate: '/frais/{id}',
            security: "object.getUser() == user",
            securityMessage: "Vous ne pouvez modifier que vos propres frais",
            processor: FraisProcessor::class,
            description: 'Modifie une note de frais existante',
            openapiContext: [
                'summary' => 'Modifie une note de frais',
                'responses' => [
                    '200' => [
                        'description' => 'Note de frais modifiée avec succès'
                    ],
                    '400' => [
                        'description' => 'Données invalides'
                    ],
                    '404' => [
                        'description' => 'Note de frais non trouvée'
                    ]
                ]
            ]
        ),
        new Delete(
            uriTemplate: '/frais/{id}',
            security: "object.getUser() == user",
            securityMessage: "Vous ne pouvez supprimer que vos propres frais",
            processor: FraisProcessor::class,
            description: 'Supprime une note de frais',
            openapiContext: [
                'summary' => 'Supprime une note de frais',
                'responses' => [

                    '404' => [
                        'description' => 'Note de frais non trouvée'
                    ]
                ]
            ]
        ),
        // Collection operation personnalisée pour mes-frais
        new GetCollection(
            uriTemplate: '/mes-frais'
        )
    ],

    normalizationContext: ['groups' => ['frais:read']],
    denormalizationContext: ['groups' => ['frais:write']]
)]
class Frais
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['frais:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: "La date est obligatoire")]
    #[Assert\Type(type: \DateTimeInterface::class, message: "Le format de la date n'est pas valide")]
    #[Assert\LessThanOrEqual(
        'today',
        message: "La date ne peut pas être dans le futur"
    )]
    #[Groups(['frais:read', 'frais:write'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le montant est obligatoire")]
    #[Assert\Positive(message: "Le montant doit être positif")]
    #[Assert\Type(type: "numeric", message: "Le montant doit être un nombre")]
    #[Assert\Range(
        min: 0.01,
        max: 999999.99,
        notInRangeMessage: "Le montant doit être entre {{ min }}€ et {{ max }}€"
    )]
    #[Groups(['frais:read', 'frais:write'])]
    private ?float $montant = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le type est obligatoire")]
    #[Assert\Choice(
        choices: ['transport', 'repas', 'hebergement', 'autres'],
        message: "Le type doit être l'un des suivants : transport, repas, hebergement, autres"
    )]
    #[Groups(['frais:read', 'frais:write'])]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: Societe::class, inversedBy: 'frais')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "La société est obligatoire")]
    #[SocieteUser]
    #[Groups(['frais:read', 'frais:write'])]
    private ?Societe $societe = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'frais')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est obligatoire")]
    #[Groups(['frais:read', 'frais:write'])]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Groups(['frais:read', 'frais:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['frais:read', 'frais:write'])]
    private ?string $justificatif = null;

    #[ORM\Column]
    #[Groups(['frais:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['frais:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(
        choices: ['en_attente', 'valide', 'refuse'],
        message: "Le statut doit être l'un des suivants : en_attente, valide, refuse"
    )]
    #[Groups(['frais:read'])]
    private string $statut = 'en_attente';

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;
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

    public function getSociete(): ?Societe
    {
        return $this->societe;
    }

    public function setSociete(?Societe $societe): static
    {
        $this->societe = $societe;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getJustificatif(): ?string
    {
        return $this->justificatif;
    }

    public function setJustificatif(?string $justificatif): static
    {
        $this->justificatif = $justificatif;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    // Méthodes utilitaires
    public function getMontantFormate(): string
    {
        return number_format($this->montant, 2, ',', ' ') . ' €';
    }

    public function getDateFormatee(): string
    {
        return $this->date ? $this->date->format('d/m/Y') : '';
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
