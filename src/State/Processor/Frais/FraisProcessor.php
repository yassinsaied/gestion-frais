<?php

namespace App\State\Processor\Frais;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Frais;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;

class FraisProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($operation instanceof Delete) {
            try {
                $this->entityManager->remove($data);
                $this->entityManager->flush();
                return ['message' => 'Le frais a été supprimé avec succès'];
            } catch (\Exception $e) {
                throw new NotFoundHttpException('Le frais demandé n\'existe pas');
            }
        }

        if ($operation instanceof Put || $operation instanceof Post) {
            if (!$data->getSociete()) {
                throw new BadRequestHttpException('La société spécifiée n\'existe pas');
            }

            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }

        return $data;
    }
}
