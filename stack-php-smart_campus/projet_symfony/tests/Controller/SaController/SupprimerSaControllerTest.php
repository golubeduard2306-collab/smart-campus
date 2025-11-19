<?php

namespace App\Tests\Controller\SaController;

use App\Entity\SystemeAcquisition;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class SupprimerSaControllerTest extends WebTestCase
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
     * Méthode helper pour créer un SA dans la base de données
     */
    private function createSa(): SystemeAcquisition
    {
        $sa = new SystemeAcquisition();
        $sa->setDateCreation(new \DateTime());
        $sa->setStatut('Inactif');

        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        return $sa;
    }

    /**
     * Test l'affichage de la page GET /supprimer-sa
     */
    public function testPageSupprimerSaEstAccessible(): void
    {
        $this->client->request('GET', '/supprimer-sa');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Supprimer un SA existant');
    }

    /**
     * Test la suppression d'un SA existant via POST
     */
    public function testSupprimerUnSaExistant(): void
    {
        // Créer un SA pour le test
        $sa = $this->createSa();
        $idSa = $sa->getId();

        // Compter le nombre de SA avant la suppression
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Soumettre le formulaire avec l'ID du SA
        $crawler = $this->client->request('GET', '/supprimer-sa');
        $form = $crawler->selectButton('Supprimer le SA')->form([
            'id' => $idSa
        ]);

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects('/supprimer-sa');
        $this->client->followRedirect();

        // Vérifier le message flash de succès
        $this->assertSelectorExists('.message.success');
        $this->assertSelectorTextContains('.message.success', "Le SA #$idSa a été supprimé");

        // Vérifier que le SA a bien été supprimé de la base de données
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant - 1, $countApres);

        // Vérifier que le SA n'existe plus
        $saSupprimer = $this->entityManager->getRepository(SystemeAcquisition::class)->find($idSa);
        $this->assertNull($saSupprimer);
    }

    /**
     * Test la tentative de suppression d'un SA inexistant
     */
    public function testSupprimerUnSaInexistant(): void
    {
        // Trouver un ID qui n'existe pas
        $maxId = $this->entityManager->getRepository(SystemeAcquisition::class)
            ->createQueryBuilder('s')
            ->select('MAX(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
        
        $idInexistant = ($maxId ?? 0) + 9999;

        // Compter le nombre de SA avant
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Soumettre le formulaire avec un ID inexistant
        $crawler = $this->client->request('GET', '/supprimer-sa');
        $form = $crawler->selectButton('Supprimer le SA')->form([
            'id' => $idInexistant
        ]);

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects('/supprimer-sa');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.message.error');
        $this->assertSelectorTextContains('.message.error', "Le SA #$idInexistant n'existe pas");

        // Vérifier qu'aucun SA n'a été supprimé
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test la validation avec un ID vide
     */
    public function testSupprimerAvecIdVide(): void
    {
        // Compter le nombre de SA avant
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Soumettre le formulaire sans ID
        $this->client->request('POST', '/supprimer-sa', [
            'id' => ''
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/supprimer-sa');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.message.error');
        $this->assertSelectorTextContains('.message.error', 'Veuillez saisir un ID valide');

        // Vérifier qu'aucun SA n'a été supprimé
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test la validation avec un ID négatif
     */
    public function testSupprimerAvecIdNegatif(): void
    {
        // Compter le nombre de SA avant
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Soumettre le formulaire avec un ID négatif
        $crawler = $this->client->request('GET', '/supprimer-sa');
        $form = $crawler->selectButton('Supprimer le SA')->form([
            'id' => -1
        ]);

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects('/supprimer-sa');
        $this->client->followRedirect();

        // Vérifier le message d'erreur (ID inexistant)
        $this->assertSelectorExists('.message.error');
        $this->assertSelectorTextContains('.message.error', "n'existe pas");

        // Vérifier qu'aucun SA n'a été supprimé
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test la suppression de plusieurs SA successifs
     */
    public function testSupprimerPlusieursSaSuccessifs(): void
    {
        // Créer 3 SA pour le test
        $sa1 = $this->createSa();
        $sa2 = $this->createSa();
        $sa3 = $this->createSa();

        $id1 = $sa1->getId();
        $id2 = $sa2->getId();
        $id3 = $sa3->getId();

        // Compter le nombre de SA avant
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Supprimer le premier SA
        $crawler = $this->client->request('GET', '/supprimer-sa');
        $form = $crawler->selectButton('Supprimer le SA')->form(['id' => $id1]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Vérifier que le premier SA est supprimé
        $this->assertSelectorTextContains('.message.success', "Le SA #$id1 a été supprimé");
        $countApres1 = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant - 1, $countApres1);

        // Supprimer le deuxième SA
        $crawler = $this->client->request('GET', '/supprimer-sa');
        $form = $crawler->selectButton('Supprimer le SA')->form(['id' => $id2]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Vérifier que le deuxième SA est supprimé
        $this->assertSelectorTextContains('.message.success', "Le SA #$id2 a été supprimé");
        $countApres2 = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant - 2, $countApres2);

        // Supprimer le troisième SA
        $crawler = $this->client->request('GET', '/supprimer-sa');
        $form = $crawler->selectButton('Supprimer le SA')->form(['id' => $id3]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Vérifier que le troisième SA est supprimé
        $this->assertSelectorTextContains('.message.success', "Le SA #$id3 a été supprimé");
        $countApres3 = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant - 3, $countApres3);
    }

    /**
     * Test que la méthode GET n'effectue pas de suppression
     */
    public function testGetNeSupprimePasDeSa(): void
    {
        // Créer un SA
        $sa = $this->createSa();

        // Compter le nombre de SA avant
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Faire une requête GET
        $this->client->request('GET', '/supprimer-sa');

        // Vérifier qu'aucun SA n'a été supprimé
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);

        // Vérifier que le SA existe toujours
        $saVerif = $this->entityManager->getRepository(SystemeAcquisition::class)->find($sa->getId());
        $this->assertNotNull($saVerif);
    }

    /**
     * Test la tentative de double suppression (supprimer le même SA deux fois)
     */
    public function testDoubleSuppression(): void
    {
        // Créer un SA
        $sa = $this->createSa();
        $idSa = $sa->getId();

        // Première suppression
        $crawler = $this->client->request('GET', '/supprimer-sa');
        $form = $crawler->selectButton('Supprimer le SA')->form(['id' => $idSa]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Vérifier le succès
        $this->assertSelectorTextContains('.message.success', "Le SA #$idSa a été supprimé");

        // Compter le nombre de SA après la première suppression
        $countApresPremiereSupp = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Deuxième suppression du même ID
        $crawler = $this->client->request('GET', '/supprimer-sa');
        $form = $crawler->selectButton('Supprimer le SA')->form(['id' => $idSa]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorTextContains('.message.error', "Le SA #$idSa n'existe pas");

        // Vérifier que le nombre de SA n'a pas changé
        $countApresDeuxiemeSupp = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countApresPremiereSupp, $countApresDeuxiemeSupp);
    }

    /**
     * Test avec un ID valide mais de type string numérique
     */
    public function testSupprimerAvecIdStringNumerique(): void
    {
        // Créer un SA
        $sa = $this->createSa();
        $idSa = $sa->getId();

        // Soumettre avec un string
        $this->client->request('POST', '/supprimer-sa', [
            'id' => (string)$idSa
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/supprimer-sa');
        $this->client->followRedirect();

        // Vérifier le succès (le contrôleur devrait gérer la conversion)
        $this->assertSelectorExists('.message.success');

        // Vérifier que le SA a été supprimé
        $saSupprimer = $this->entityManager->getRepository(SystemeAcquisition::class)->find($idSa);
        $this->assertNull($saSupprimer);
    }
}
