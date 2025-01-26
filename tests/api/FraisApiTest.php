<?php

namespace App\Tests\Api;

use App\Entity\Frais;
use App\Entity\User;
use App\Entity\Societe;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class FraisApiTest extends ApiTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;
    private $users = [];
    private $societes = [];
    private $jwtManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->jwtManager = static::getContainer()->get('lexik_jwt_authentication.jwt_manager');

        $this->cleanDatabase();
        $this->createTestUser();
    }

    private function createTestUser(): void
    {

        $user = new User();
        $user->setEmail('john.doe@example.com')
            ->setIdentifiant('john.doe')
            ->setPassword($this->passwordHasher->hashPassword($user, 'password123'))
            ->setNom('Doe')
            ->setPrenom('John')
            ->setDateNaissance(new \DateTime('1990-01-01'))
            ->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);

        // Créer une société
        $societe = new Societe();
        $societe->setNom('Tech Solutions')
            ->setAdresse('1 rue de test')
            ->setSiret('12345678901234')
            ->setEmail('contact@techsolutions.fr')
            ->setTelephone('0123456789');

        $this->entityManager->persist($societe);

        // Lier l'utilisateur à la société
        $societe->addCommercial($user);

        $this->entityManager->flush();

        // Sauvegarder les références
        $this->users[] = $user;
        $this->societes[] = $societe;

        // Créer des frais pour l'utilisateur
        $this->createFraisForUser($user, $societe, 5);
    }

    private function createFraisForUser(User $user, Societe $societe, int $count): void
    {
        $types = ['transport', 'repas', 'hebergement', 'autres'];

        for ($i = 0; $i < $count; $i++) {
            $frais = new Frais();
            $frais->setDate(new \DateTime("-" . rand(1, 30) . " days"))
                ->setMontant(mt_rand(1000, 50000) / 100)
                ->setType($types[array_rand($types)])
                ->setDescription("Test frais " . ($i + 1))
                ->setUser($user)
                ->setSociete($societe)
                ->setStatut('en_attente');

            $this->entityManager->persist($frais);
        }
        $this->entityManager->flush();
    }

    public function testGetMesFraisForFirstUser(): void
    {
        // Connecter l'utilisateur
        $this->client->loginUser($this->users[0]);

        // Faire la requête à l'API
        $response = $this->client->request('GET', '/api/mes-frais');

        // Vérifier que la réponse est un succès
        $this->assertResponseIsSuccessful();

        // Vérifier le contenu de la réponse
        $data = $response->toArray();

        // Vérifier qu'il y a bien 5 frais
        $this->assertCount(5, $data['hydra:member']);
        $this->assertEquals(5, $data['hydra:totalItems']);

        // Vérifier la structure de base de la réponse
        $this->assertArrayHasKey('@context', $data);
        $this->assertArrayHasKey('@id', $data);
        $this->assertArrayHasKey('@type', $data);
        $this->assertArrayHasKey('hydra:member', $data);

        // Vérifier la structure d'un frais
        $premierFrais = $data['hydra:member'][0];
        $this->assertArrayHasKey('montant', $premierFrais);
        $this->assertArrayHasKey('type', $premierFrais);
        $this->assertArrayHasKey('description', $premierFrais);
        $this->assertArrayHasKey('statut', $premierFrais);
    }

    public function testCreateFrais(): void
    {
        // Connecter l'utilisateur
        $this->client->loginUser($this->users[0]);

        $payload = [
            "date" => "2024-01-15",
            "montant" => "122.50",
            "type" => "transport",
            "description" => "Taxi aéroport",
            "societe" => sprintf("/api/societes/%d", $this->societes[0]->getId()),
            "statut" => "en_attente",
            "user" => sprintf("/api/users/%d", $this->users[0]->getId())
        ];

        try {
            $response = $this->client->request('POST', '/api/frais', [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/ld+json',
                    'Accept' => 'application/ld+json'
                ]
            ]);
        } catch (\Exception $e) {

            throw $e;
        }

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();
        $this->assertEquals("122.50", $data['montant']);
        $this->assertEquals("transport", $data['type']);
        $this->assertEquals("Taxi aéroport", $data['description']);
        $this->assertEquals("en_attente", $data['statut']);
    }

    private function getAuthenticatedHeaders(): array
    {
        $token = $this->jwtManager->create($this->users[0]);

        return [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/ld+json',
            'Accept' => 'application/ld+json',
        ];
    }

    public function testUpdateFrais(): void
    {

        $response = $this->client->request('GET', '/api/mes-frais', ['headers' => $this->getAuthenticatedHeaders()]);
        $fraisData = $response->toArray()['hydra:member'][0];
        $fraisId = $fraisData['id'];

        $payload = [
            "date" => "2024-01-20",
            "montant" => "150.75",
            "type" => "repas",
            "description" => "Déjeuner d'affaires modifié",
            "societe" => sprintf("/api/societes/%d", $this->societes[0]->getId()),
            "user" => sprintf("/api/users/%d", $this->users[0]->getId())
        ];

        $response = $this->client->request('PUT', '/api/frais/' . $fraisId, [
            'json' => $payload,
            'headers' => $this->getAuthenticatedHeaders()
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        $this->assertEquals("150.75", $data['montant']);
        $this->assertEquals("repas", $data['type']);
        $this->assertEquals("Déjeuner d'affaires modifié", $data['description']);
    }

    public function testDeleteFrais(): void
    {

        $response = $this->client->request('GET', '/api/mes-frais', ['headers' => $this->getAuthenticatedHeaders()]);
        $fraisData = $response->toArray()['hydra:member'][0];
        $fraisId = $fraisData['id'];

        $this->client->request('DELETE', '/api/frais/' . $fraisId, [
            'headers' => $this->getAuthenticatedHeaders()
        ]);

        $this->assertResponseIsSuccessful();

        try {
            $this->client->request('GET', '/api/frais/' . $fraisId, [
                'headers' => $this->getAuthenticatedHeaders()
            ]);
            $this->fail('Le frais existe encore');
        } catch (\Exception $e) {
            $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        }
    }

    public function testGetSingleFrais(): void
    {

        $response = $this->client->request('GET', '/api/mes-frais', ['headers' => $this->getAuthenticatedHeaders()]);
        $fraisData = $response->toArray()['hydra:member'][0];
        $fraisId = $fraisData['id'];

        $response = $this->client->request('GET', '/api/frais/' . $fraisId, [
            'headers' => $this->getAuthenticatedHeaders()
        ]);

        $this->assertResponseIsSuccessful();
        $data = $response->toArray();

        $this->assertArrayHasKey('@context', $data);
        $this->assertArrayHasKey('@id', $data);
        $this->assertArrayHasKey('@type', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($fraisId, $data['id']);
    }

    private function cleanDatabase(): void
    {
        $this->entityManager->createQuery('DELETE FROM App\Entity\Frais')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Societe')->execute();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanDatabase();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
