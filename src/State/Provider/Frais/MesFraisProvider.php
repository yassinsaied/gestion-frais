<?php

namespace App\State\Provider\Frais;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\FraisRepository;
use Symfony\Bundle\SecurityBundle\Security;


class MesFraisProvider implements ProviderInterface
{
    public function __construct(
        private FraisRepository $fraisRepository,
        private Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        // Récupère l'utilisateur connecté
        $user = $this->security->getUser();

        // Initialise les variables
        $userId = null;
        $userEmail = 'non connecté';

        // Si un utilisateur est connecté
        if ($user !== null) {
            // Exemple : user.id = 1
            $userId = $user->getId();
            // Exemple : user.email = "john@example.com"
            $userEmail = $user->getEmail();
        }


        // Retourne les frais de l'utilisateur
        return $this->fraisRepository->findBy(['user' => $user]);
    }
}
