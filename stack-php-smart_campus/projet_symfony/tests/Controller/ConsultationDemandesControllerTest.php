<?php

namespace App\Tests\Controller;

use App\Entity\Salle;
use App\Entity\SystemeAcquisition;
use App\Entity\Demande;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class ConsultationDemandesControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Nettoyer la base de données avant chaque test
        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /**
     * Méthode helper pour nettoyer la base de données
     */
    private function cleanDatabase(): void
    {
        // Supprimer d'abord les demandes (dépendantes des salles et SA)
        $demandes = $this->entityManager->getRepository(Demande::class)->findAll();
        foreach ($demandes as $demande) {
            $this->entityManager->remove($demande);
        }
        $this->entityManager->flush();

        // Ensuite supprimer les salles (qui peuvent référencer des SA)
        $salles = $this->entityManager->getRepository(Salle::class)->findAll();
        foreach ($salles as $salle) {
            // Détacher le SA si présent
            if ($salle->getSaId() !== null) {
                $salle->setSaId(null);
            }
            $this->entityManager->remove($salle);
        }
        $this->entityManager->flush();

        // Enfin supprimer tous les SA
        $systemes = $this->entityManager->getRepository(SystemeAcquisition::class)->findAll();
        foreach ($systemes as $systeme) {
            $this->entityManager->remove($systeme);
        }
        $this->entityManager->flush();
        
        $this->entityManager->clear();
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
     * Méthode helper pour créer un SA dans la base de données
     */
    private function createSa(string $statut = 'Inactif'): SystemeAcquisition
    {
        $sa = new SystemeAcquisition();
        $sa->setStatut($statut);
        $sa->setDateCreation(new \DateTime());

        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        return $sa;
    }

    /**
     * Méthode helper pour créer une demande dans la base de données
     */
    private function createDemande(Salle $salle, SystemeAcquisition $sa, string $type, string $statut = 'En cours'): Demande
    {
        $demande = new Demande();
        $demande->setTypeDemande($type);
        $demande->setDateDemande(new \DateTime());
        $demande->setStatut($statut);
        $demande->setIdSalle($salle);
        $demande->setIdSa($sa);

        $this->entityManager->persist($demande);
        $this->entityManager->flush();

        return $demande;
    }

    /**
     * Test l'affichage de la page de consultation des demandes
     */
    public function testPageConsultationAccessible(): void
    {
        $this->client->request('GET', '/consultation-demandes');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Consultation demandes');
    }

    /**
     * Test le filtrage des demandes
     */
    public function testFiltrageDemandes(): void
    {
        $salle1 = $this->createSalle('S1');
        $sa1 = $this->createSa('Inactif');
        $this->createDemande($salle1, $sa1, 'Installation', 'En cours');

        $salle2 = $this->createSalle('S2');
        $sa2 = $this->createSa('Actif');
        $this->createDemande($salle2, $sa2, 'Désinstallation', 'En cours');

        // Test filtre 'tout' (par défaut)
        $crawler = $this->client->request('GET', '/consultation-demandes');
        $this->assertResponseIsSuccessful();
        // On devrait voir les deux demandes (vérification basique sur le texte ou le nombre d'éléments)
        // Supposons qu'il y a une ligne par demande dans un tableau
        $this->assertCount(2, $crawler->filter('tbody tr'));

        // Test filtre 'installation'
        $crawler = $this->client->request('GET', '/consultation-demandes', ['filtre' => 'installation']);
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('tbody tr'));
        $this->assertSelectorTextContains('tbody tr', 'Installation');

        // Test filtre 'désinstallation'
        $crawler = $this->client->request('GET', '/consultation-demandes', ['filtre' => 'désinstallation']);
        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('tbody tr'));
        $this->assertSelectorTextContains('tbody tr', 'Désinstallation');
    }

    /**
     * Test la validation d'une demande d'installation
     */
    public function testValidationDemandeInstallation(): void
    {
        $salle = $this->createSalle('S3');
        $sa = $this->createSa('Inactif');
        $demande = $this->createDemande($salle, $sa, 'Installation', 'En cours');
        $demandeId = $demande->getId();

        // Clear pour être sûr
        $this->entityManager->clear();

        // Action de validation
        $this->client->request('POST', '/consultation-demandes/archiver/' . $demandeId);
        
        $this->assertResponseRedirects('/consultation-demandes');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');

        // Vérifications en base
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $demandeRefresh = $em->getRepository(Demande::class)->find($demandeId);
        $saRefresh = $em->getRepository(SystemeAcquisition::class)->find($sa->getId());
        $salleRefresh = $em->getRepository(Salle::class)->find($salle->getId());

        // La demande doit être terminée
        $this->assertEquals('Terminé', $demandeRefresh->getStatut());
        
        // Le SA doit être Actif
        $this->assertEquals('Actif', $saRefresh->getStatut());
        
        // Le SA doit être lié à la salle
        $this->assertNotNull($salleRefresh->getSaId());
        $this->assertEquals($sa->getId(), $salleRefresh->getSaId()->getId());
    }

    /**
     * Test la validation d'une demande de désinstallation
     */
    public function testValidationDemandeDesinstallation(): void
    {
        $salle = $this->createSalle('S4');
        $sa = $this->createSa('Actif');
        $salle->setSaId($sa);
        $this->entityManager->flush();
        
        $demande = $this->createDemande($salle, $sa, 'Désinstallation', 'En cours');
        $demandeId = $demande->getId();

        // Clear pour être sûr
        $this->entityManager->clear();

        // Action de validation
        $this->client->request('POST', '/consultation-demandes/archiver/' . $demandeId);
        
        $this->assertResponseRedirects('/consultation-demandes');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');

        // Vérifications en base
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $demandeRefresh = $em->getRepository(Demande::class)->find($demandeId);
        $saRefresh = $em->getRepository(SystemeAcquisition::class)->find($sa->getId());
        $salleRefresh = $em->getRepository(Salle::class)->find($salle->getId());

        // La demande doit être terminée
        $this->assertEquals('Terminé', $demandeRefresh->getStatut());
        
        // Le SA doit être Inactif
        $this->assertEquals('Inactif', $saRefresh->getStatut());
        
        // Le SA ne doit plus être lié à la salle
        $this->assertNull($salleRefresh->getSaId());
    }

    /**
     * Test la validation d'une demande inexistante
     */
    public function testValidationDemandeInexistante(): void
    {
        $this->client->request('POST', '/consultation-demandes/archiver/999999');
        
        $this->assertResponseRedirects('/consultation-demandes');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-error');
    }
}
