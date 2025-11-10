<?php

namespace App\DataFixtures;

use App\Entity\Demande; // Gardé si vous l'utilisez plus tard
use App\Entity\Salle;
// 1. Importer la classe de base Fixture
use Doctrine\Bundle\FixturesBundle\Fixture;
// 2. Importer l'interface ObjectManager pour la méthode load
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture // 1. La classe doit étendre Fixture
{
    // La méthode load doit respecter l'interface (utiliser ObjectManager)
    public function load(ObjectManager $manager): void
    {
        $salle = new Salle();
        $salle->setNomSalle("D005");
        $salle->setDateCreation(new \DateTime());
        $salle->setDateModification(new \DateTime());
        $salle->setEtage(0);
        $salle->setNbFenetres(3);
        $manager->persist($salle);

        $manager->flush();
    }
}