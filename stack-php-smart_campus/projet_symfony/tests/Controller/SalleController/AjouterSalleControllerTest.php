<?php

namespace App\Tests\Controller;

use App\Entity\Salle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AjouterSalleControllerTest extends WebTestCase
{
    public function testPageAjoutSalleAccessible()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/ajouter-salle');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorTextContains('form button[type="submit"]', 'Ajouter la salle');
    }

    public function testErreurNomSalleInvalide()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/ajouter-salle');

        self::assertGreaterThan(0, $crawler->filter('form')->count(), 'Formulaire d\'ajout introuvable');
        $form = $crawler->filter('form')->form();

        $input = $crawler->filter('input[name*="nom_salle"]');
        self::assertGreaterThan(0, $input->count(), 'Champ "nom_salle" introuvable dans le formulaire');
        $fieldName = $input->attr('name');

        $form[$fieldName] = 'BAD_NAME_999';

        $client->followRedirects(true);
        $client->submit($form);

        self::assertSelectorExists('.message.error');
    }

    public function testErreurSalleExisteDeja()
    {
        // Préparer l'entité existante
        // Récupérer l'EntityManager depuis le conteneur du client (évite de booter le kernel deux fois)
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $salle = new Salle();
        $salle->setNomSalle('D201');
        $salle->setEtage(2);
        $salle->setNbFenetres(0);
        $salle->setDateCreation(new \DateTime());

        $em->persist($salle);
        $em->flush();

        // Soumettre le formulaire avec le même nom
        $crawler = $client->request('GET', '/ajouter-salle');

        self::assertGreaterThan(0, $crawler->filter('form')->count(), 'Formulaire d\'ajout introuvable');
        $form = $crawler->filter('form')->form();

        $input = $crawler->filter('input[name*="nom_salle"]');
        self::assertGreaterThan(0, $input->count(), 'Champ "nom_salle" introuvable dans le formulaire');
        $fieldName = $input->attr('name');

        // Remplir aussi les champs attendus par le contrôleur
        $form[$fieldName] = 'D201';
        // Le contrôleur lit également 'etage' et 'nb_fenetres' : les fournir
        if (isset($form['etage'])) {
            $form['etage'] = 2;
        } else {
            // certains formulaires nomment les champs différemment (ex: form[etage])
            try {
                $form->setValues(array_merge($form->getValues(), ['etage' => 2]));
            } catch (\Throwable $e) {
                // ignore si impossible
            }
        }
        if (isset($form['nb_fenetres'])) {
            $form['nb_fenetres'] = 0;
        }

        $client->followRedirects(true);
        $client->submit($form);

        self::assertSelectorExists('.message.error');
    }

    public function testChampsObligatoires(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/ajouter-salle');

        self::assertGreaterThan(0, $crawler->filter('form')->count(), 'Formulaire d\'ajout introuvable');
        $form = $crawler->filter('form')->form();

        $input = $crawler->filter('input[name*="nom_salle"]');
        self::assertGreaterThan(0, $input->count(), 'Champ "nom_salle" introuvable dans le formulaire');
        $fieldName = $input->attr('name');

        // Envoyer des champs vides
        $form[$fieldName] = '';
        if (isset($form['etage'])) {
            $form['etage'] = '';
        }
        if (isset($form['nb_fenetres'])) {
            $form['nb_fenetres'] = '';
        }

        $client->followRedirects(true);
        $client->submit($form);

        self::assertSelectorExists('.message.error');
    }

    public function testSalleVisibleDansSelection(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $nom = 'TEST_SEL_'.uniqid();
        $salle = new Salle();
        $salle->setNomSalle($nom);
        $salle->setEtage(1);
        $salle->setNbFenetres(0);
        $salle->setDateCreation(new \DateTime());

        $em->persist($salle);
        $em->flush();

        $crawler = $client->request('GET', '/selectionner-salle');
        $content = $client->getResponse()->getContent();

        self::assertStringContainsString($nom, $content, 'La salle ajoutée doit apparaître sur la page de sélection');
    }
}
