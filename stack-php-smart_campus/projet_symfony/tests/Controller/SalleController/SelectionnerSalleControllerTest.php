<?php

namespace App\Tests\Controller\SalleController;

use App\Entity\Salle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SelectionnerSalleControllerTest extends WebTestCase
{
    public function testPageSelectionEstAccessible(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/selectionner-salle');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Sélectionner une salle');
    }

    public function testSalleCreeeApparaîtDansLaListe(): void
    {
        $client = static::createClient();
        $conteneur = $client->getContainer();
        /** @var EntityManagerInterface $gestionnaireEntites */
        $gestionnaireEntites = $conteneur->get(EntityManagerInterface::class);

        $nom = 'SEL_TEST_'.uniqid();
        $salle = new Salle();
        $salle->setNomSalle($nom);
        $salle->setEtage(1);
        $salle->setNbFenetres(0);
        $salle->setDateCreation(new \DateTime());

        $gestionnaireEntites->persist($salle);
        $gestionnaireEntites->flush();

        $crawler = $client->request('GET', '/selectionner-salle');
        $content = $client->getResponse()->getContent();

        self::assertStringContainsString($nom, $content);
    }

    public function testSalleSupprimeePlusVisible(): void
    {
        $client = static::createClient();
        $conteneur = $client->getContainer();
        /** @var EntityManagerInterface $gestionnaireEntites */
        $gestionnaireEntites = $conteneur->get(EntityManagerInterface::class);

        $nom = 'SEL_DEL_'.uniqid();
        $salle = new Salle();
        $salle->setNomSalle($nom);
        $salle->setEtage(1);
        $salle->setNbFenetres(0);
        $salle->setDateCreation(new \DateTime());

        $gestionnaireEntites->persist($salle);
        $gestionnaireEntites->flush();

        // vérifier présente
        $explorateur = $client->request('GET', '/selectionner-salle');
        self::assertStringContainsString($nom, $client->getResponse()->getContent());

        // supprimer via la page info-salle (flux actuellement implémenté)
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/info-salle/' . urlencode($nom));

        // trouver et soumettre le formulaire de suppression (input hidden action=supprimer)
        $formNode = $crawler->filter('form')->reduce(function ($node) {
            return $node->filter('input[name="action"][value="supprimer"]')->count() > 0;
        });

        if ($formNode->count() > 0) {
            $form = $formNode->first()->form();
            $client->submit($form);
        } else {
            // si aucun formulaire spécifique, tenter une soumission manuelle
            $client->request('POST', '/info-salle/' . urlencode($nom), ['action' => 'supprimer']);
        }

        // vérifier absente
        $crawler = $client->request('GET', '/selectionner-salle');
        self::assertStringNotContainsString($nom, $client->getResponse()->getContent());
    }

    public function testChampRechercheExiste(): void
    {
        $client = static::createClient();
        $client->request('GET', '/selectionner-salle');

        self::assertSelectorExists('#searchInput');
    }

    public function testChaqueSallePossedeBoutonEtAction(): void
    {
        $client = static::createClient();
        $conteneur = $client->getContainer();
        /** @var EntityManagerInterface $gestionnaireEntites */
        $gestionnaireEntites = $conteneur->get(EntityManagerInterface::class);

        // create multiple salles
        $names = [];
        for ($i = 0; $i < 3; $i++) {
            $names[] = 'BTN_TEST_'.uniqid();
            $s = new Salle();
            $s->setNomSalle($names[$i]);
            $s->setEtage(1);
            $s->setNbFenetres(0);
            $s->setDateCreation(new \DateTime());
                $gestionnaireEntites->persist($s);
        }
        $gestionnaireEntites->flush();

        $explorateur = $client->request('GET', '/selectionner-salle');

        // Compter les boutons dans le tableau
        $boutons = $explorateur->filter('table#sallesTable button.btn');
        self::assertGreaterThanOrEqual(3, $boutons->count(), 'Chaque salle doit avoir un bouton de sélection');

        // Vérifier que chaque action pointe vers info-salle/{nom}
        $contenu = $client->getResponse()->getContent();
        foreach ($names as $name) {
            self::assertStringContainsString('/info-salle/'.$name, $contenu);
        }
    }

    public function testMessageAucuneSalleQuandVide(): void
    {
        $client = static::createClient();
        $conteneur = $client->getContainer();
        /** @var EntityManagerInterface $gestionnaireEntites */
        $gestionnaireEntites = $conteneur->get(EntityManagerInterface::class);

        // Supprimer toutes les salles pour simuler une base vide
        $listeSalles = $gestionnaireEntites->getRepository(Salle::class)->findAll();
        foreach ($listeSalles as $s) {
            $gestionnaireEntites->remove($s);
        }
        $gestionnaireEntites->flush();

        $client->request('GET', '/selectionner-salle');
        $contenu = $client->getResponse()->getContent();

        self::assertStringContainsString('Aucune salle', $contenu);
    }
}
