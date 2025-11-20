<?php

namespace App\Tests\Controller\SalleController;

use App\Entity\Salle;
use App\Entity\SystemeAcquisition;
use App\Entity\Demande;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class InfoSalleControllerTest extends WebTestCase
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
     * Test l'affichage de la page GET /info-salle/{nomSalle}
     */
    public function testPageInfoSalleEstAccessible(): void
    {
        $salle = $this->createSalle('D206');

        $this->client->request('GET', '/info-salle/D206');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'D206');
    }

    /**
     * Test l'affichage d'une salle inexistante
     */
    public function testPageInfoSalleInexistante(): void
    {
        $this->client->request('GET', '/info-salle/SalleInexistante123');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Test l'affichage des informations d'une salle sans SA installé
     */
    public function testAffichageSalleSansSa(): void
    {
        $salle = $this->createSalle('A101', 1, 3);

        $this->client->request('GET', '/info-salle/A101');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'A101');
        $this->assertSelectorTextContains('body', 'Aucun SA n\'est installé dans la salle A101');
    }

    /**
     * Test l'affichage d'une salle avec SA installé
     */
    public function testAffichageSalleAvecSaInstalle(): void
    {
        $salle = $this->createSalle('B202');
        $sa = $this->createSa('Actif');
        
        $salle->setSaId($sa);
        $this->entityManager->flush();

        $this->client->request('GET', '/info-salle/B202');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'B202');
    }

    /**
     * Test la création d'une demande d'installation
     */
    public function testCreerDemandeInstallation(): void
    {
        $salle = $this->createSalle('C303');
        $sa = $this->createSa('Inactif');
        $salleId = $salle->getId();

        // Compter le nombre de demandes avant
        $countAvant = $this->entityManager->getRepository(Demande::class)->count([]);

        // Soumettre le formulaire d'installation
        $this->client->request('POST', '/info-salle/C303', [
            'action' => 'installer'
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/info-salle/C303');
        $this->client->followRedirect();

        // Vérifier le message de succès
        $this->assertSelectorExists('.alert.alert-success');
        $this->assertSelectorTextContains('.alert.alert-success', 'Demande d\'installation créée avec succès');

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier qu'une demande a été créée
        $countApres = $em->getRepository(Demande::class)->count([]);
        $this->assertEquals($countAvant + 1, $countApres);

        // Vérifier les propriétés de la demande
        $salleRefresh = $em->getRepository(Salle::class)->find($salleId);
        $demande = $em->getRepository(Demande::class)
            ->findOneBy(['idSalle' => $salleRefresh], ['id' => 'DESC']);
        
        $this->assertNotNull($demande);
        $this->assertEquals('Installation', $demande->getTypeDemande());
        $this->assertEquals('En cours', $demande->getStatut());
        $this->assertEquals($salleId, $demande->getIdSalle()->getId());
    }

    /**
     * Test la création d'une demande d'installation sans SA disponible
     */
    public function testCreerDemandeInstallationSansSaDisponible(): void
    {
        $salle = $this->createSalle('D404');

        // Compter le nombre de demandes avant
        $countAvant = $this->entityManager->getRepository(Demande::class)->count([]);

        // Soumettre le formulaire d'installation
        $this->client->request('POST', '/info-salle/D404', [
            'action' => 'installer'
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/info-salle/D404');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.alert.alert-error');
        $this->assertSelectorTextContains('.alert.alert-error', 'Aucun système d\'acquisition inactif disponible');

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier qu'aucune demande n'a été créée
        $countApres = $em->getRepository(Demande::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test la création d'une demande d'installation alors qu'un SA est déjà installé
     */
    public function testCreerDemandeInstallationAvecSaDejaInstalle(): void
    {
        $salle = $this->createSalle('E505');
        $sa = $this->createSa('Actif');
        $salle->setSaId($sa);
        $this->entityManager->flush();

        // Créer un autre SA inactif disponible
        $saDisponible = $this->createSa('Inactif');

        // Compter le nombre de demandes avant
        $countAvant = $this->entityManager->getRepository(Demande::class)->count([]);

        // Soumettre le formulaire d'installation
        $this->client->request('POST', '/info-salle/E505', [
            'action' => 'installer'
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/info-salle/E505');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.alert.alert-error');
        $this->assertSelectorTextContains('.alert.alert-error', 'Un système d\'acquisition est déjà installé dans cette salle');

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier qu'aucune demande n'a été créée
        $countApres = $em->getRepository(Demande::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test la création d'une demande de désinstallation
     */
    public function testCreerDemandeDesinstallation(): void
    {
        $salle = $this->createSalle('F606');
        $sa = $this->createSa('Actif');
        $salle->setSaId($sa);
        $this->entityManager->flush();
        $salleId = $salle->getId();

        // Compter le nombre de demandes avant
        $countAvant = $this->entityManager->getRepository(Demande::class)->count([]);

        // Soumettre le formulaire de désinstallation
        $this->client->request('POST', '/info-salle/F606', [
            'action' => 'desinstaller'
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/info-salle/F606');
        $this->client->followRedirect();

        // Vérifier le message de succès
        $this->assertSelectorExists('.alert.alert-success');
        $this->assertSelectorTextContains('.alert.alert-success', 'Demande de désinstallation créée avec succès');

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier qu'une demande a été créée
        $countApres = $em->getRepository(Demande::class)->count([]);
        $this->assertEquals($countAvant + 1, $countApres);

        // Vérifier les propriétés de la demande
        $salleRefresh = $em->getRepository(Salle::class)->find($salleId);
        $demande = $em->getRepository(Demande::class)
            ->findOneBy(['idSalle' => $salleRefresh], ['id' => 'DESC']);
        
        $this->assertNotNull($demande);
        $this->assertEquals('Désinstallation', $demande->getTypeDemande());
        $this->assertEquals('En cours', $demande->getStatut());
    }

    /**
     * Test la création d'une demande de désinstallation sans SA installé
     */
    public function testCreerDemandeDesinstallationSansSaInstalle(): void
    {
        $salle = $this->createSalle('G707');

        // Compter le nombre de demandes avant
        $countAvant = $this->entityManager->getRepository(Demande::class)->count([]);

        // Soumettre le formulaire de désinstallation
        $this->client->request('POST', '/info-salle/G707', [
            'action' => 'desinstaller'
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/info-salle/G707');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.alert.alert-error');
        $this->assertSelectorTextContains('.alert.alert-error', 'Aucun système d\'acquisition n\'est installé dans cette salle');

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier qu'aucune demande n'a été créée
        $countApres = $em->getRepository(Demande::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test l'annulation d'une demande en cours
     */
    public function testAnnulerDemandeEnCours(): void
    {
        $salle = $this->createSalle('H808');
        $sa = $this->createSa('Inactif');
        $demande = $this->createDemande($salle, $sa, 'Installation', 'En cours');
        $demandeId = $demande->getId();

        // Compter le nombre de demandes avant
        $countAvant = $this->entityManager->getRepository(Demande::class)->count([]);

        // Clear l'entity manager pour forcer le rechargement depuis la BD
        $this->entityManager->clear();

        // Soumettre le formulaire d'annulation
        $this->client->request('POST', '/info-salle/H808', [
            'action' => 'annuler'
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/info-salle/H808');
        $this->client->followRedirect();

        // Vérifier le message de succès
        $this->assertSelectorExists('.alert.alert-success');
        $this->assertSelectorTextContains('.alert.alert-success', 'Demande annulée avec succès');

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier que la demande a été supprimée
        $countApres = $em->getRepository(Demande::class)->count([]);
        $this->assertEquals($countAvant - 1, $countApres);

        // Vérifier que la demande n'existe plus
        $demandeSupprimer = $em->getRepository(Demande::class)->find($demandeId);
        $this->assertNull($demandeSupprimer);
    }

    /**
     * Test l'impossibilité de créer une nouvelle demande quand une demande est en cours
     */
    public function testCreerDemandeQuandDemandeEnCours(): void
    {
        $salle = $this->createSalle('I909');
        $sa1 = $this->createSa('Inactif');
        $sa2 = $this->createSa('Inactif');
        
        // Créer une demande en cours
        $this->createDemande($salle, $sa1, 'Installation', 'En cours');

        // Compter le nombre de demandes avant
        $countAvant = $this->entityManager->getRepository(Demande::class)->count([]);

        // Clear l'entity manager pour forcer le rechargement depuis la BD
        $this->entityManager->clear();

        // Tenter de créer une nouvelle demande d'installation
        $this->client->request('POST', '/info-salle/I909', [
            'action' => 'installer'
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/info-salle/I909');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.alert.alert-error');
        $this->assertSelectorTextContains('.alert.alert-error', 'Une demande est déjà en cours pour cette salle');

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier qu'aucune nouvelle demande n'a été créée
        $countApres = $em->getRepository(Demande::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test la suppression d'une salle sans SA et sans demande
     */
    public function testSupprimerSalleSansSaSansDemande(): void
    {
        $salle = $this->createSalle('J1010');

        // Compter le nombre de salles avant
        $countAvant = $this->entityManager->getRepository(Salle::class)->count([]);

        // Soumettre le formulaire de suppression
        $this->client->request('POST', '/info-salle/J1010', [
            'action' => 'supprimer'
        ]);

        // Vérifier la redirection vers la page de sélection de salle
        $this->assertResponseRedirects('/selectionner-salle');

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier que la salle a été supprimée
        $countApres = $em->getRepository(Salle::class)->count([]);
        $this->assertEquals($countAvant - 1, $countApres);
    }

    /**
     * Test l'impossibilité de supprimer une salle avec un SA installé
     */
    public function testSupprimerSalleAvecSaInstalle(): void
    {
        $salle = $this->createSalle('K1111');
        $sa = $this->createSa('Actif');
        $salle->setSaId($sa);
        $this->entityManager->flush();

        // Compter le nombre de salles avant
        $countAvant = $this->entityManager->getRepository(Salle::class)->count([]);

        // Soumettre le formulaire de suppression
        $this->client->request('POST', '/info-salle/K1111', [
            'action' => 'supprimer'
        ]);

        // Vérifier la redirection
        $this->assertResponseRedirects('/info-salle/K1111');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.alert.alert-error');
        $this->assertSelectorTextContains('.alert.alert-error', 'Impossible de supprimer la salle : un système d\'acquisition est installé');

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier que la salle n'a pas été supprimée
        $countApres = $em->getRepository(Salle::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test l'impossibilité de supprimer une salle avec une demande en cours
     */
    public function testSupprimerSalleAvecDemandeEnCours(): void
    {
        $salle = $this->createSalle('L1212');
        $sa = $this->createSa('Inactif');
        $this->createDemande($salle, $sa, 'Installation', 'En cours');

        // Compter le nombre de salles avant
        $countAvant = $this->entityManager->getRepository(Salle::class)->count([]);

        // Clear l'entity manager pour forcer le rechargement depuis la BD
        $this->entityManager->clear();

        // Soumettre le formulaire de suppression
        $this->client->request('POST', '/info-salle/L1212', [
            'action' => 'supprimer'
        ]);

        // Vérifier la redirection - le controller vérifie d'abord les demandes en cours
        $this->assertResponseRedirects('/info-salle/L1212');
        $this->client->followRedirect();

        // Vérifier le message d'erreur
        $this->assertSelectorExists('.alert.alert-error');
        $this->assertSelectorTextContains('.alert.alert-error', 'Impossible de supprimer la salle : une demande est en cours');

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier que la salle n'a pas été supprimée
        $countApres = $em->getRepository(Salle::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    /**
     * Test la suppression d'une salle avec des demandes anciennes (non en cours)
     */
    public function testSupprimerSalleAvecDemandesAnciennes(): void
    {
        $salle = $this->createSalle('M1313');
        $sa = $this->createSa('Inactif');
        
        // Créer des demandes terminées
        $this->createDemande($salle, $sa, 'Installation', 'Terminée');
        $this->createDemande($salle, $sa, 'Désinstallation', 'Terminée');

        // Compter le nombre de salles et demandes avant
        $countSallesAvant = $this->entityManager->getRepository(Salle::class)->count([]);
        $countDemandesAvant = $this->entityManager->getRepository(Demande::class)->count([]);

        // Soumettre le formulaire de suppression
        $this->client->request('POST', '/info-salle/M1313', [
            'action' => 'supprimer'
        ]);

        // Vérifier la redirection vers la page de sélection de salle
        $this->assertResponseRedirects('/selectionner-salle');

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier que la salle a été supprimée
        $countSallesApres = $em->getRepository(Salle::class)->count([]);
        $this->assertEquals($countSallesAvant - 1, $countSallesApres);

        // Vérifier que les demandes anciennes ont été supprimées
        $countDemandesApres = $em->getRepository(Demande::class)->count([]);
        $this->assertEquals($countDemandesAvant - 2, $countDemandesApres);
    }

    /**
     * Test que la méthode GET n'effectue pas d'action
     */
    public function testGetNEffectuePasAction(): void
    {
        $salle = $this->createSalle('N1414');
        $sa = $this->createSa('Inactif');

        // Compter le nombre de demandes avant
        $countAvant = $this->entityManager->getRepository(Demande::class)->count([]);

        // Faire une requête GET (ne devrait pas créer de demande)
        $this->client->request('GET', '/info-salle/N1414');

        $this->assertResponseIsSuccessful();

        // Récupérer un nouveau EntityManager pour voir les changements
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Vérifier qu'aucune demande n'a été créée
        $countApres = $em->getRepository(Demande::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }
}
