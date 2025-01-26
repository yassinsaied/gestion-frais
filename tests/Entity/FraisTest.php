<?php

namespace App\Tests\Entity;

use App\Entity\Frais;
use App\Entity\User;
use App\Entity\Societe;
use PHPUnit\Framework\TestCase;

class FraisTest extends TestCase
{
    private Frais $frais;

    protected function setUp(): void
    {
        $this->frais = new Frais();
    }

    public function testConstructeur(): void
    {
        // Vérifie que createdAt est initialisé
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->frais->getCreatedAt());
        $this->assertEquals('en_attente', $this->frais->getStatut());
    }

    public function testSettersGetters(): void
    {
        // Test date
        $date = new \DateTime('2024-03-20');
        $this->frais->setDate($date);
        $this->assertEquals($date, $this->frais->getDate());

        // Test montant
        $this->frais->setMontant(150.50);
        $this->assertEquals(150.50, $this->frais->getMontant());

        // Test type
        $this->frais->setType('transport');
        $this->assertEquals('transport', $this->frais->getType());

        // Test description
        $this->frais->setDescription('Test description');
        $this->assertEquals('Test description', $this->frais->getDescription());

        // Test statut
        $this->frais->setStatut('valide');
        $this->assertEquals('valide', $this->frais->getStatut());
    }

    public function testRelations(): void
    {
        $user = new User();
        $societe = new Societe();

        $this->frais->setUser($user);
        $this->frais->setSociete($societe);

        $this->assertSame($user, $this->frais->getUser());
        $this->assertSame($societe, $this->frais->getSociete());
    }

    public function testFormatage(): void
    {
        // Test getMontantFormate
        $this->frais->setMontant(1234.56);
        $this->assertEquals('1 234,56 €', $this->frais->getMontantFormate());

        // Test getDateFormatee
        $date = new \DateTime('2024-03-20');
        $this->frais->setDate($date);
        $this->assertEquals('20/03/2024', $this->frais->getDateFormatee());
    }

    public function testPreUpdate(): void
    {
        $this->assertNull($this->frais->getUpdatedAt());

        $this->frais->setUpdatedAtValue();

        $this->assertInstanceOf(\DateTimeImmutable::class, $this->frais->getUpdatedAt());
    }
}
