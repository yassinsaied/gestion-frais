<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Societe;
use App\Entity\Frais;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const PASSWORD = 'password123';
    private const NB_FRAIS_PAR_USER = 10;

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Création des utilisateurs
        $users = [];
        $userData = [
            ['john.doe@example.com', 'john.doe', 'Doe', 'John'],
            ['jane.smith@example.com', 'jane.smith', 'Smith', 'Jane'],
            ['robert.martin@example.com', 'robert.martin', 'Martin', 'Robert'],
            ['alice.dupont@example.com', 'alice.dupont', 'Dupont', 'Alice'],
            ['marc.dubois@example.com', 'marc.dubois', 'Dubois', 'Marc']
        ];

        foreach ($userData as $data) {
            $user = new User();
            $user->setEmail($data[0]);
            $user->setIdentifiant($data[1]);
            $user->setPassword($this->passwordHasher->hashPassword($user, self::PASSWORD));
            $user->setNom($data[2]);
            $user->setPrenom($data[3]);
            $user->setDateNaissance(new \DateTime('1990-01-01'));
            $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
            $users[] = $user;
        }

        // Création des sociétés
        $societes = [];
        $societeData = [
            // Sociétés pour John Doe
            ['Tech Solutions', 'Paris', '12345678901234', 0],
            ['Marketing Plus', 'Lyon', '98765432109876', 0],

            // Sociétés pour Jane Smith
            ['Consulting Pro', 'Bordeaux', '45678901234567', 1],
            ['Digital Agency', 'Marseille', '78901234567890', 1],

            // Sociétés pour Robert Martin
            ['Innovation Corp', 'Lille', '23456789012345', 2],
            ['Web Services', 'Toulouse', '34567890123456', 2],

            // Sociétés pour Alice Dupont
            ['Data Systems', 'Nantes', '56789012345678', 3],
            ['Cloud Tech', 'Nice', '67890123456789', 3],

            // Sociétés pour Marc Dubois
            ['Smart Solutions', 'Strasbourg', '89012345678901', 4],
            ['Digital Hub', 'Rennes', '90123456789012', 4]
        ];

        foreach ($societeData as $data) {
            $societe = new Societe();
            $societe->setNom($data[0]);
            $societe->setAdresse($data[1]);
            $societe->setSiret($data[2]);
            $societe->setEmail('contact@' . strtolower(str_replace(' ', '', $data[0])) . '.fr');
            $societe->setTelephone('0123456789');

            // Attribuer la société à l'utilisateur spécifique
            $societe->addCommercial($users[$data[3]]);

            $manager->persist($societe);
            $societes[] = $societe;
        }

        // Types de frais et descriptions
        $types = ['transport', 'repas', 'hebergement', 'autres'];
        $descriptions = [
            'transport' => ['Train Paris-Lyon', 'Taxi aéroport', 'Location voiture', 'Essence'],
            'repas' => ['Déjeuner client', 'Dîner équipe', 'Petit-déjeuner business', 'Restaurant'],
            'hebergement' => ['Hôtel Paris', 'Hôtel Lyon', 'Hôtel Marseille', 'Airbnb'],
            'autres' => ['Fournitures bureau', 'Matériel informatique', 'Téléphone', 'Internet']
        ];

        // Création des frais pour chaque utilisateur
        foreach ($users as $userIndex => $user) {
            // Récupérer uniquement les sociétés de l'utilisateur
            $userSocietes = array_filter($societes, function ($societe) use ($user) {
                return $societe->getCommerciaux()->contains($user);
            });

            if (empty($userSocietes)) {
                continue; // Skip si l'utilisateur n'a pas de sociétés
            }

            // Créer des frais pour chaque utilisateur
            for ($i = 0; $i < self::NB_FRAIS_PAR_USER; $i++) {
                $type = $types[array_rand($types)];
                $frais = new Frais();
                $frais->setDate(new \DateTime("-" . rand(1, 30) . " days"));
                $frais->setMontant(mt_rand(1000, 50000) / 100);
                $frais->setType($type);
                $frais->setDescription($descriptions[$type][array_rand($descriptions[$type])]);
                $frais->setUser($user);
                $frais->setSociete($userSocietes[array_rand($userSocietes)]);
                $frais->setStatut(['en_attente', 'valide', 'refuse'][rand(0, 2)]);

                $manager->persist($frais);
            }
        }

        $manager->flush();

        // Afficher les informations de connexion
        echo "\nUtilisateurs créés (password: " . self::PASSWORD . ") :\n";
        foreach ($users as $user) {
            echo "- {$user->getEmail()}\n";
        }

        // Afficher les sociétés par utilisateur
        echo "\nSociétés par utilisateur :\n";
        foreach ($users as $user) {
            echo "\n{$user->getPrenom()} {$user->getNom()} :\n";
            foreach ($societes as $societe) {
                if ($societe->getCommerciaux()->contains($user)) {
                    echo "- {$societe->getNom()} ({$societe->getAdresse()})\n";
                }
            }
        }
    }
}
