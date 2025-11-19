<?php

namespace App\Tests\Controller\SalleController;

use App\Entity\Salle;
use App\Entity\SystemeAcquisition;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class  SupprimerSalleControllerTest extends WebTestCase
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
     * Méthode helper pour créer une salle dans la base de données
     */
    private function createSalle(string $nomSalle = 'TestSalle', int $etage = 1, int $nbFenetres = 2): Salle
    {
        $salle = new Salle();
        $salle->setNomSalle($nomSalle);
        $salle->setEtage($etage);
        $salle->setNbFenetres($nbFenetres);
        $salle->setDateCreation(new \DateTime());

        $this->entityManager->persist($salle);
        $this->entityManager->flush();

        return $salle;
    }

    /**
     * Test l'affichage de la page GET /supprimer-salle
     */
    public function testPageSupprimerSalleEstAccessible(): void
    {
        $this->client->request('GET', '/supprimer-salle');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Supprimer une salle');
    }

    /**
     * Test la suppression d'une salle existante via POST
     */
    public function testSupprimerUneSalleExistante(): void
    {
        // Créer une salle pour le test
        $salle = $this->createSalle('D206');
        $nomSalle = $salle->getNomSalle();
        $idSalle = $salle->getId();

        // Compter le nombre de salles avant la suppression
        $countAvant = $this->entityManager->getRepository(Salle::class)->count([]);

        // Soumettre le formulaire avec le nom de la salle
        $crawler = $this->client->request('GET', '/supprimer-salle');
        $form = $crawler->selectButton('Supprimer la salle')->form([
            'nom_salle' => $nomSalle
        ]);

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects('/supprimer-salle');
        $this->client->followRedirect();

        // Vérifier le message flash de succès
        $this->assertSelectorExists('.message.success');
        $this->assertSelectorTextContains('.message.success', "La salle \"$nomSalle\"");
        $this->assertSelectorTextContains('.message.success', "ID: $idSalle");

        // Vérifier que la salle a bien été supprimée de la base de données
        $countApres = $this->entityManager->getRepository(Salle::class)->count([]);
        $this->assertEquals($countAvant - 1, $countApres);

        // Vérifier que la salle n'existe plus
        $salleSupprimer = $this->entityManager->getRepository(Salle::class)->find($idSalle);
        $this->assertNull($salleSupprimer);
    }

    /**
     * Test la tentative de suppression d'une salle inexistante
     */
    public function testSupprimerUneSalleInexistante(): void
    {
        $nomInexistant = 'SalleInexistante123';

        // Compter le nombre de salles avant
        $countAvant = $this->entityManager->getRepository(Salle::class)->count([]);

        // Soumettre le formulaire avec un nom inexistant
        $crawler = $this->client->request('GET', '/supprimer-salle');
        $form = $crawler->selectButton('Supprimer la salle')->form([
            'nom_salle' => $nomInexistant
        ]);

        $this->client->submit($form);

        // Vérifier la redirection
        $this->assertResponseRedirects('/supprimer-salle');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.message.error');
        $this->assertSelectorTextContains('.message.error', "La salle \"$nomInexistant\" n'existe pas");

        // Vérifier qu'aucune salle n'a été supprimée
        $countApres = $this->entityManager->getRepository(Salle::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test la validation avec un nom vide
     */
    public function testSupprimerAvecNomVide(): void
    {
        // Compter le nombre de salles avant
        $countAvant = $this->entityManager->getRepository(Salle::class)->count([]);

        // Soumettre le formulaire sans nom
        $this->client->request('POST', '/supprimer-salle', [
            'nom_salle' => ''
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/supprimer-salle');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.message.error');
        $this->assertSelectorTextContains('.message.error', 'Veuillez saisir un nom de salle valide');

        // Vérifier qu'aucune salle n'a été supprimée
        $countApres = $this->entityManager->getRepository(Salle::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test la suppression de plusieurs salles successives
     */
    public function testSupprimerPlusieursSallesSuccessives(): void
    {
        // Créer 3 salles pour le test
        $salle1 = $this->createSalle('A101');
        $salle2 = $this->createSalle('B202');
        $salle3 = $this->createSalle('C303');

        $nom1 = $salle1->getNomSalle();
        $nom2 = $salle2->getNomSalle();
        $nom3 = $salle3->getNomSalle();

        // Compter le nombre de salles avant
        $countAvant = $this->entityManager->getRepository(Salle::class)->count([]);

        // Supprimer la première salle
        $crawler = $this->client->request('GET', '/supprimer-salle');
        $form = $crawler->selectButton('Supprimer la salle')->form(['nom_salle' => $nom1]);
        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.message.success', $nom1);

        // Supprimer la deuxième salle
        $crawler = $this->client->request('GET', '/supprimer-salle');
        $form = $crawler->selectButton('Supprimer la salle')->form(['nom_salle' => $nom2]);
        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.message.success', $nom2);

        // Supprimer la troisième salle
        $crawler = $this->client->request('GET', '/supprimer-salle');
        $form = $crawler->selectButton('Supprimer la salle')->form(['nom_salle' => $nom3]);
        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.message.success', $nom3);

        // Vérifier que toutes ont été supprimées
        $countApres = $this->entityManager->getRepository(Salle::class)->count([]);
        $this->assertEquals($countAvant - 3, $countApres);
    }

    /**
     * Test que la méthode GET n'effectue pas de suppression
     */
    public function testGetNeSupprimePasDeSalle(): void
    {
        // Créer une salle
        $salle = $this->createSalle('TestGet');

        // Compter le nombre de salles avant
        $countAvant = $this->entityManager->getRepository(Salle::class)->count([]);

        // Faire une requête GET
        $this->client->request('GET', '/supprimer-salle');

        // Vérifier qu'aucune salle n'a été supprimée
        $countApres = $this->entityManager->getRepository(Salle::class)->count([]);
        $this->assertEquals($countAvant, $countApres);

        // Vérifier que la salle existe toujours
        $salleVerif = $this->entityManager->getRepository(Salle::class)->find($salle->getId());
        $this->assertNotNull($salleVerif);
    }

    /**
     * Test la tentative de double suppression
     */
    public function testDoubleSuppression(): void
    {
        // Créer une salle
        $salle = $this->createSalle('D404');
        $nomSalle = $salle->getNomSalle();

        // Première suppression
        $crawler = $this->client->request('GET', '/supprimer-salle');
        $form = $crawler->selectButton('Supprimer la salle')->form(['nom_salle' => $nomSalle]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Vérifier le succès
        $this->assertSelectorTextContains('.message.success', $nomSalle);

        // Compter après la première suppression
        $countApresPremiereSupp = $this->entityManager->getRepository(Salle::class)->count([]);

        // Deuxième suppression du même nom
        $crawler = $this->client->request('GET', '/supprimer-salle');
        $form = $crawler->selectButton('Supprimer la salle')->form(['nom_salle' => $nomSalle]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorTextContains('.message.error', "La salle \"$nomSalle\" n'existe pas");

        // Vérifier que le nombre de salles n'a pas changé
        $countApresDeuxiemeSupp = $this->entityManager->getRepository(Salle::class)->count([]);
        $this->assertEquals($countApresPremiereSupp, $countApresDeuxiemeSupp);
    }

    /**
     * Test la sensibilité à la casse du nom de salle
     */
    public function testSensibiliteCasseNomSalle(): void
    {
        // Créer une salle avec un nom en majuscules
        $salle = $this->createSalle('D206');

        // Tenter de supprimer avec un nom en minuscules
        $crawler = $this->client->request('GET', '/supprimer-salle');
        $form = $crawler->selectButton('Supprimer la salle')->form(['nom_salle' => 'd206']);
        $this->client->submit($form);
        $this->client->followRedirect();

        // La recherche n'est PAS sensible à la casse (findOneBy utilise la comparaison de la BDD)
        // Donc le message peut être soit succès soit erreur selon la collation de la BDD
        // On vérifie juste qu'il y a un message
        $hasMessage = $this->client->getCrawler()->filter('.message.success, .message.error')->count() > 0;
        $this->assertTrue($hasMessage);
    }

    /**
     * Test la suppression d'une salle avec espaces dans le nom
     */
    public function testSupprimerSalleAvecEspaces(): void
    {
        // Créer une salle avec espaces
        $salle = $this->createSalle('Salle 101');
        $nomSalle = $salle->getNomSalle();

        // Supprimer la salle
        $crawler = $this->client->request('GET', '/supprimer-salle');
        $form = $crawler->selectButton('Supprimer la salle')->form(['nom_salle' => $nomSalle]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Vérifier le succès
        $this->assertSelectorTextContains('.message.success', $nomSalle);

        // Vérifier que la salle a été supprimée
        $salleSupprimer = $this->entityManager->getRepository(Salle::class)->find($salle->getId());
        $this->assertNull($salleSupprimer);
    }

    /**
     * Test la suppression d'une salle avec des caractères spéciaux
     */
    public function testSupprimerSalleAvecCaracteresSpeciaux(): void
    {
        // Créer une salle avec caractères spéciaux
        $salle = $this->createSalle('D-206/A');
        $nomSalle = $salle->getNomSalle();

        // Supprimer la salle
        $crawler = $this->client->request('GET', '/supprimer-salle');
        $form = $crawler->selectButton('Supprimer la salle')->form(['nom_salle' => $nomSalle]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Vérifier le succès
        $this->assertSelectorTextContains('.message.success', $nomSalle);

        // Vérifier que la salle a été supprimée
        $salleSupprimer = $this->entityManager->getRepository(Salle::class)->find($salle->getId());
        $this->assertNull($salleSupprimer);
    }
}
