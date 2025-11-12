<?php


namespace App\Controller\SalleController;


use App\Entity\Demande;
use App\Entity\Salle;
use App\Entity\SystemeAcquisition;
use App\Repository\SalleRepository;
use App\Repository\SystemeAcquisitionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InfoSalleController extends AbstractController
{
    // 1. Mise à jour de la route : Utilisez un nom simple pour le paramètre
    #[Route('/info-salle/{nomSalle}', name: 'app_info_salle', methods: ['GET', 'POST'])]
    public function index(
        // 2. Injection directe du paramètre de route (le nom de la salle)
        string $nomSalle,
        SalleRepository $salleRepository,
        SystemeAcquisitionRepository $systemeAcquisitionRepository,
        Request $request,
        EntityManagerInterface $manager
    ): Response
    {
        // 3. Récupération de la salle en utilisant le paramètre injecté $nomSalle
        $salle = $salleRepository->findOneBy(['nom_salle' => $nomSalle]);
        
        if(!$salle){
            // Le createNotFoundException lance une NotFoundHttpException (404)
            throw $this->createNotFoundException('La salle "' . $nomSalle . '" n\'existe pas !');
        }

        // Vérifier s'il y a une demande en cours pour cette salle
        $demandeEnCours = null;
        foreach ($salle->getDemandes() as $demande) {
            if ($demande->getStatut() === 'En cours') {
                $demandeEnCours = $demande;
                break;
            }
        }

        // Traitement des actions POST
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');

            if ($action === 'installer') {
                // Créer une demande d'installation
                $saInactif = $systemeAcquisitionRepository->findOneBy(['statut' => 'Inactif']);
                if ($saInactif) {
                    $demande = new Demande();
                    $demande->setTypeDemande('Installation');
                    $demande->setDateDemande(new \DateTime());
                    $demande->setStatut('En cours');
                    $demande->setIdSalle($salle);
                    $demande->setIdSa($saInactif);

                    $manager->persist($demande);
                    $manager->flush();

                    $this->addFlash('success', 'Demande d\'installation créée avec succès.');
                } else {
                    $this->addFlash('error', 'Aucun système d\'acquisition inactif disponible.');
                }
                return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
            }

            if ($action === 'desinstaller') {
                // Créer une demande de désinstallation
                $demande = new Demande();
                $demande->setTypeDemande('Désinstallation');
                $demande->setDateDemande(new \DateTime());
                $demande->setStatut('En cours');
                $demande->setIdSalle($salle);
                // Trouver le SA actif associé à cette salle
                $saActif = $systemeAcquisitionRepository->findOneBy(['statut' => 'Actif']);
                if ($saActif) {
                    $demande->setIdSa($saActif);
                }

                $manager->persist($demande);
                $manager->flush();

                $this->addFlash('success', 'Demande de désinstallation créée avec succès.');
                return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
            }

            if ($action === 'annuler' && $demandeEnCours) {
                // Annuler la demande en cours
                $manager->remove($demandeEnCours);
                $manager->flush();

                $this->addFlash('success', 'Demande annulée avec succès.');
                return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
            }
        }

        // Vérifier si un SA est installé dans cette salle
        $saInstalle = null;
        foreach ($salle->getDemandes() as $demande) {
            if ($demande->getTypeDemande() === 'Installation' && $demande->getStatut() === 'Terminé') {
                $saInstalle = $demande->getIdSa();
                break;
            }
        }

        return $this->render('info_salle/index.html.twig', [
            'controller_name' => 'InfoSalleController',
            'salle' => $salle,
            'demandeEnCours' => $demandeEnCours,
            'saInstalle' => $saInstalle,
        ]);
    }
}
