<?php

namespace App\Tests\Controller\SalleController;

use App\Entity\Salle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ModifierSalleControllerTest extends WebTestCase
{
    public function testPageModifierSalleEstAccessible(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $nom = 'MOD_ACCESS_'.uniqid();
        $salle = new Salle();
        $salle->setNomSalle($nom);
        $salle->setEtage(1);
        $salle->setNbFenetres(0);
        $salle->setDateCreation(new \DateTime());

        $em->persist($salle);
        $em->flush();

        $client->request('GET', '/modifier-salle/' . urlencode($nom));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Modifier la salle');
    }

    public function testChampsModifiablesSontPresents(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $nom = 'MOD_TEST_'.uniqid();
        $salle = new Salle();
        $salle->setNomSalle($nom);
        $salle->setEtage(2);
        $salle->setNbFenetres(3);
        $salle->setDateCreation(new \DateTime());

        $em->persist($salle);
        $em->flush();

        $crawler = $client->request('GET', '/modifier-salle/' . urlencode($nom));

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="nom_salle"]');
        self::assertSelectorExists('input[name="etage"]');
        self::assertSelectorExists('input[name="nb_fenetres"]');

        // values present
        $this->assertStringContainsString($nom, $client->getResponse()->getContent());
        $this->assertStringContainsString('value="2"', $client->getResponse()->getContent());
        $this->assertStringContainsString('value="3"', $client->getResponse()->getContent());
    }

    public function testVerificationUniciteNomSurModification(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        // Create two valid names that match the controller's validation pattern (e.g. D1xx)
        $etage = 1;
        $repo = $em->getRepository(Salle::class);

        do {
            $suf1 = random_int(10, 99);
            $nomA = 'D' . $etage . sprintf('%02d', $suf1);
        } while ($repo->findOneBy(['nom_salle' => $nomA]));

        do {
            $suf2 = random_int(10, 99);
            $nomB = 'D' . $etage . sprintf('%02d', $suf2);
        } while ($nomB === $nomA || $repo->findOneBy(['nom_salle' => $nomB]));

        $a = new Salle();
        $a->setNomSalle($nomA);
        $a->setEtage($etage);
        $a->setNbFenetres(1);
        $a->setDateCreation(new \DateTime());

        $b = new Salle();
        $b->setNomSalle($nomB);
        $b->setEtage($etage);
        $b->setNbFenetres(1);
        $b->setDateCreation(new \DateTime());

        $em->persist($a);
        $em->persist($b);
        $em->flush();

        // Attempt to rename A to B
        $crawler = $client->request('GET', '/modifier-salle/' . urlencode($nomA));
        $form = $crawler->selectButton('Confirmer les modifications')->form([
            'nom_salle' => $nomB,
            'etage' => $etage,
            'nb_fenetres' => 1,
        ]);

        $client->submit($form);

        // Should display an error about duplicate name
        self::assertSelectorTextContains('.message', 'Ce nom de salle est déjà utilisé');
    }

    public function testChampsObligatoiresNePeuventPasEtreVidés(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $nom = 'MOD_REQ_'.uniqid();
        $salle = new Salle();
        $salle->setNomSalle($nom);
        $salle->setEtage(1);
        $salle->setNbFenetres(1);
        $salle->setDateCreation(new \DateTime());

        $em->persist($salle);
        $em->flush();

        $crawler = $client->request('GET', '/modifier-salle/' . urlencode($nom));
        $form = $crawler->selectButton('Confirmer les modifications')->form([
            'nom_salle' => '',
            'etage' => '',
            'nb_fenetres' => '',
        ]);

        $client->submit($form);

        // Controller should add a flash error (preg_match or required validation)
        self::assertSelectorExists('.message');
        $content = $client->getResponse()->getContent();
        self::assertStringContainsString('Le nom de la salle', $content);
    }

    public function testPageModifierAccessibleDepuisInfoSalle(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $nom = 'MOD_LINK_'.uniqid();
        $salle = new Salle();
        $salle->setNomSalle($nom);
        $salle->setEtage(1);
        $salle->setNbFenetres(0);
        $salle->setDateCreation(new \DateTime());

        $em->persist($salle);
        $em->flush();

        $crawler = $client->request('GET', '/info-salle/' . urlencode($nom));

        // The template uses a button that redirects to modifier-salle; assert the URL exists in content
        $content = $client->getResponse()->getContent();
        self::assertStringContainsString('/modifier-salle/' . $nom, $content);
    }
}
