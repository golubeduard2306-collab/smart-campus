<?php

namespace App\Tests\Controller\SaController;

use App\Entity\SystemeAcquisition;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class AjouterSaControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /**
     * Test l'affichage de la page GET /ajouter-sa
     */
    public function testPageAjouterSaEstAccessible(): void
    {
        $this->client->request('GET', '/ajouter-sa');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter un nouveau SA');
    }

    /**
     * Test l'ajout d'un seul SA via POST
     */
    public function testAjouterUnSeulSa(): void
    {
        // Compter le nombre de SA avant l'ajout
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Soumettre le formulaire avec quantité = 1 (par défaut)
        $crawler = $this->client->request('GET', '/ajouter-sa');
        $form = $crawler->selectButton('Ajouter SA')->form([
            'quantite' => 1
        ]);

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects('/ajouter-sa');
        $this->client->followRedirect();

        // Vérifier le message flash de succès
        $this->assertSelectorExists('.message');
        $this->assertSelectorTextContains('.message', 'Un nouveau SA a été ajouté');

        // Vérifier qu'un SA a bien été ajouté à la base de données
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant + 1, $countApres);

        // Vérifier les propriétés du dernier SA ajouté
        $dernierSa = $this->entityManager->getRepository(SystemeAcquisition::class)
            ->findOneBy([], ['id' => 'DESC']);
        
        $this->assertNotNull($dernierSa);
        $this->assertEquals('Inactif', $dernierSa->getStatut());
        $this->assertInstanceOf(\DateTime::class, $dernierSa->getDateCreation());
        
        // Vérifier que la date de création est aujourd'hui
        $today = new \DateTime();
        $this->assertEquals(
            $today->format('Y-m-d'),
            $dernierSa->getDateCreation()->format('Y-m-d')
        );
    }

    /**
     * Test l'ajout de plusieurs SA via POST
     */
    public function testAjouterPlusieursSa(): void
    {
        $quantite = 5;
        
        // Compter le nombre de SA avant l'ajout
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Soumettre le formulaire avec quantité = 5
        $crawler = $this->client->request('GET', '/ajouter-sa');
        $form = $crawler->selectButton('Ajouter SA')->form([
            'quantite' => $quantite
        ]);

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects('/ajouter-sa');
        $this->client->followRedirect();

        // Vérifier le message flash de succès
        $this->assertSelectorExists('.message');
        $this->assertSelectorTextContains('.message', "$quantite nouveaux SA ont été ajoutés");

        // Vérifier que les SA ont bien été ajoutés à la base de données
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant + $quantite, $countApres);
    }

    /**
     * Test la validation de la quantité (valeur trop petite)
     */
    public function testQuantiteTropPetite(): void
    {
        // Compter le nombre de SA avant
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Soumettre le formulaire avec quantité = 0
        $crawler = $this->client->request('GET', '/ajouter-sa');
        $form = $crawler->selectButton('Ajouter SA')->form([
            'quantite' => 0
        ]);

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects('/ajouter-sa');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.error-message');
        $this->assertSelectorTextContains('.error-message', 'La quantité doit être entre 1 et 100');

        // Vérifier qu'aucun SA n'a été ajouté
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test la validation de la quantité (valeur trop grande)
     */
    public function testQuantiteTropGrande(): void
    {
        // Compter le nombre de SA avant
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Soumettre le formulaire avec quantité = 101
        $crawler = $this->client->request('GET', '/ajouter-sa');
        $form = $crawler->selectButton('Ajouter SA')->form([
            'quantite' => 101
        ]);

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects('/ajouter-sa');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.error-message');
        $this->assertSelectorTextContains('.error-message', 'La quantité doit être entre 1 et 100');

        // Vérifier qu'aucun SA n'a été ajouté
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test l'ajout de SA avec la quantité maximale autorisée
     */
    public function testAjouterQuantiteMaximale(): void
    {
        $quantite = 100;
        
        // Compter le nombre de SA avant l'ajout
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Soumettre le formulaire avec quantité = 100
        $crawler = $this->client->request('GET', '/ajouter-sa');
        $form = $crawler->selectButton('Ajouter SA')->form([
            'quantite' => $quantite
        ]);

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects('/ajouter-sa');
        $this->client->followRedirect();

        // Vérifier le message flash de succès
        $this->assertSelectorExists('.message');

        // Vérifier que les SA ont bien été ajoutés
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant + $quantite, $countApres);
    }

    /**
     * Test le statut par défaut des SA ajoutés
     */
    public function testStatutParDefautEstInactif(): void
    {
        // Ajouter un SA
        $crawler = $this->client->request('GET', '/ajouter-sa');
        $form = $crawler->selectButton('Ajouter SA')->form([
            'quantite' => 1
        ]);
        $this->client->submit($form);

        // Récupérer le dernier SA ajouté
        $dernierSa = $this->entityManager->getRepository(SystemeAcquisition::class)
            ->findOneBy([], ['id' => 'DESC']);

        // Vérifier que le statut est bien "Inactif"
        $this->assertEquals('Inactif', $dernierSa->getStatut());
    }

    /**
     * Test que la méthode GET n'ajoute pas de SA
     */
    public function testGetNAjoutePasDeSa(): void
    {
        // Compter le nombre de SA avant
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Faire une requête GET
        $this->client->request('GET', '/ajouter-sa');

        // Vérifier qu'aucun SA n'a été ajouté
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }
}
