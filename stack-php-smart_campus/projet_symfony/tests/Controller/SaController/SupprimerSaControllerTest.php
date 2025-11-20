<?php

namespace App\Tests\Controller\SaController;

use App\Entity\SystemeAcquisition;
use App\Entity\Salle;
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

    private function createSa(): SystemeAcquisition
    {
        $sa = new SystemeAcquisition();
        $sa->setDateCreation(new \DateTime());
        $sa->setStatut('Inactif');

        $this->entityManager->persist($sa);
        $this->entityManager->flush();

        return $sa;
    }

    private function createSalle(string $nomSalle = 'TestSalle'): Salle
    {
        $salle = new Salle();
        $salle->setNomSalle($nomSalle);
        $salle->setEtage(1);
        $salle->setNbFenetres(2);
        $salle->setDateCreation(new \DateTime());

        $this->entityManager->persist($salle);
        $this->entityManager->flush();

        return $salle;
    }

    public function testPageSupprimerSaEstAccessible(): void
    {
        $this->client->request('GET', '/supprimer-sa');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Supprimer un SA existant');
    }

    public function testPageAfficheLaListeDesSa(): void
    {
        $sa1 = $this->createSa();
        $sa2 = $this->createSa();
        $sa3 = $this->createSa();

        $this->client->request('GET', '/supprimer-sa');

        $this->assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString((string)$sa1->getId(), $content);
        $this->assertStringContainsString((string)$sa2->getId(), $content);
        $this->assertStringContainsString((string)$sa3->getId(), $content);
    }

    public function testSupprimerUnSaNonAssigne(): void
    {
        $sa = $this->createSa();
        $idSa = $sa->getId();

        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        $this->client->request('POST', '/supprimer-sa', ['id' => $idSa]);

        $this->assertResponseRedirects('/supprimer-sa');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', "Le SA #$idSa a été supprimé");

        $this->entityManager->clear();
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant - 1, $countApres);

        $saSupprimer = $this->entityManager->getRepository(SystemeAcquisition::class)->find($idSa);
        $this->assertNull($saSupprimer);
    }

    public function testNePeutPasSupprimerUnSaAssigne(): void
    {
        $sa = $this->createSa();
        $salle = $this->createSalle('D206');
        $idSa = $sa->getId();

        $salle->setSaId($sa);
        $this->entityManager->persist($salle);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Recharger les entités
        $sa = $this->entityManager->find(SystemeAcquisition::class, $idSa);
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        // Vérifier que le SA est bien lié à la salle
        $this->assertNotNull($sa->getSalle());
        $this->assertEquals('D206', $sa->getSalle()->getNomSalle());

        $this->client->request('POST', '/supprimer-sa', ['id' => $idSa]);

        $this->assertResponseRedirects('/supprimer-sa');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-error');
        $this->assertSelectorTextContains('.alert-error', "Impossible de supprimer le SA #$idSa");
        $this->assertSelectorTextContains('.alert-error', 'il est assigné à la salle "D206"');

        $this->entityManager->clear();
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);

        $saVerif = $this->entityManager->getRepository(SystemeAcquisition::class)->find($idSa);
        $this->assertNotNull($saVerif);
    }

    public function testSupprimerUnSaInexistant(): void
    {
        $maxId = $this->entityManager->getRepository(SystemeAcquisition::class)
            ->createQueryBuilder('s')
            ->select('MAX(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
        
        $idInexistant = ($maxId ?? 0) + 9999;

        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        $this->client->request('POST', '/supprimer-sa', ['id' => $idInexistant]);

        $this->assertResponseRedirects('/supprimer-sa');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-error');
        $this->assertSelectorTextContains('.alert-error', "Le SA #$idInexistant n'existe pas");

        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    public function testSupprimerAvecIdVide(): void
    {
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        $this->client->request('POST', '/supprimer-sa', ['id' => '']);

        $this->assertResponseRedirects('/supprimer-sa');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-error');
        $this->assertSelectorTextContains('.alert-error', 'Veuillez saisir un ID valide');

        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    public function testSupprimerAvecIdNegatif(): void
    {
        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        $this->client->request('POST', '/supprimer-sa', ['id' => -1]);

        $this->assertResponseRedirects('/supprimer-sa');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-error');
        $this->assertSelectorTextContains('.alert-error', "n'existe pas");

        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);
    }

    public function testSupprimerPlusieursSaNonAssignesSuccessifs(): void
    {
        $sa1 = $this->createSa();
        $sa2 = $this->createSa();
        $sa3 = $this->createSa();

        $id1 = $sa1->getId();
        $id2 = $sa2->getId();
        $id3 = $sa3->getId();

        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        $this->client->request('POST', '/supprimer-sa', ['id' => $id1]);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "Le SA #$id1 a été supprimé");

        $this->entityManager->clear();
        $countApres1 = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant - 1, $countApres1);

        $this->client->request('POST', '/supprimer-sa', ['id' => $id2]);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "Le SA #$id2 a été supprimé");

        $this->entityManager->clear();
        $countApres2 = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant - 2, $countApres2);

        $this->client->request('POST', '/supprimer-sa', ['id' => $id3]);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "Le SA #$id3 a été supprimé");

        $this->entityManager->clear();
        $countApres3 = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant - 3, $countApres3);
    }

    public function testSupprimerUnSaApresDesassignation(): void
    {
        $salle = $this->createSalle('TestSalle');
        $sa = $this->createSa();
        $idSa = $sa->getId();

        $salle->setSaId($sa);
        $this->entityManager->persist($salle);
        $this->entityManager->flush();

        $salle->setSaId(null);
        $this->entityManager->persist($salle);
        $this->entityManager->flush();

        $this->client->request('POST', '/supprimer-sa', ['id' => $idSa]);
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', "Le SA #$idSa a été supprimé");

        $this->entityManager->clear();
        $saSupprimer = $this->entityManager->getRepository(SystemeAcquisition::class)->find($idSa);
        $this->assertNull($saSupprimer);
    }

    public function testGetNeSupprimePasDeSa(): void
    {
        $sa = $this->createSa();

        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        $this->client->request('GET', '/supprimer-sa');

        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant, $countApres);

        $saVerif = $this->entityManager->getRepository(SystemeAcquisition::class)->find($sa->getId());
        $this->assertNotNull($saVerif);
    }

    public function testDoubleSuppression(): void
    {
        $sa = $this->createSa();
        $idSa = $sa->getId();

        $this->client->request('POST', '/supprimer-sa', ['id' => $idSa]);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert-success', "Le SA #$idSa a été supprimé");

        $this->entityManager->clear();
        $countApresPremiereSupp = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        $this->client->request('POST', '/supprimer-sa', ['id' => $idSa]);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert-error', "Le SA #$idSa n'existe pas");

        $countApresDeuxiemeSupp = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countApresPremiereSupp, $countApresDeuxiemeSupp);
    }

    public function testSupprimerAvecIdStringNumerique(): void
    {
        $sa = $this->createSa();
        $idSa = $sa->getId();

        $this->client->request('POST', '/supprimer-sa', ['id' => (string)$idSa]);

        $this->assertResponseRedirects('/supprimer-sa');
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success');

        $this->entityManager->clear();
        $saSupprimer = $this->entityManager->getRepository(SystemeAcquisition::class)->find($idSa);
        $this->assertNull($saSupprimer);
    }

    public function testMessageErreurContiensNomSalle(): void
    {
        $sa = $this->createSa();
        $salle = $this->createSalle('Amphithéâtre A');
        $idSa = $sa->getId();

        $salle->setSaId($sa);
        $this->entityManager->persist($salle);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request('POST', '/supprimer-sa', ['id' => $idSa]);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert-error', 'Amphithéâtre A');
    }

    public function testSupprimerMelangeSaAssignesEtNonAssignes(): void
    {
        $sa1 = $this->createSa();
        $sa2 = $this->createSa();
        $sa3 = $this->createSa();

        $id1 = $sa1->getId();
        $id2 = $sa2->getId();
        $id3 = $sa3->getId();

        $salle = $this->createSalle('TestSalle');
        $salle->setSaId($sa2);
        $this->entityManager->persist($salle);
        $this->entityManager->flush();

        $countAvant = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);

        $this->client->request('POST', '/supprimer-sa', ['id' => $id1]);
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $this->client->request('POST', '/supprimer-sa', ['id' => $id2]);
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-error');
        $this->assertSelectorTextContains('.alert-error', 'Impossible de supprimer');

        $this->client->request('POST', '/supprimer-sa', ['id' => $id3]);
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');

        $this->entityManager->clear();
        $countApres = $this->entityManager->getRepository(SystemeAcquisition::class)->count([]);
        $this->assertEquals($countAvant - 2, $countApres);
    }
}
