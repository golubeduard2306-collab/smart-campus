<?php


namespace App\Controller\SalleController;

use App\Entity\Demande;
use App\Entity\Salle;
use App\Entity\SystemeAcquisition;
use App\Repository\SalleRepository;
use App\Repository\SystemeAcquisitionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; // Optionnel, mais bonne pratique

final class InfoSalleController extends AbstractController
{
    // 1. Mise à jour de la route : Utilisez un nom simple pour le paramètre
    #[Route('/info-salle/{nomSalle}', name: 'app_info_salle')]
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
        $SA = $systemeAcquisitionRepository->findOneBy(['statut' => 'Inactif']);
        if(!$salle){
            // Le createNotFoundException lance une NotFoundHttpException (404)
            throw $this->createNotFoundException('La salle "' . $nomSalle . '" n\'existe pas !');
        }

        // Vérifier s'il y a une demande en attente pour cette salle
        $demandeEnCours = $manager->getRepository(Demande::class)
            ->findOneBy(['id_salle' => $salle->getId(), 'statut' => 'En attente']);

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            
            if ($action === 'installer') {
                // Installation d'un SA
                $demande = new Demande();
                $demande->setTypeDemande('installation');
                $demande->setDateDemande(new \DateTime());
                $demande->setIdSalle($salle);
                $demande->setIdSa($SA);
                $demande->setStatut('En attente');
                $manager->persist($demande);
                $manager->flush();

                $this->addFlash('success', 'Demande d\'installation créée !');
            }
            elseif ($action === 'annuler' && $demandeEnCours) {
                // Annuler la demande
                $manager->remove($demandeEnCours);
                $manager->flush();

                $this->addFlash('success', 'Demande annulée avec succès !');
            }
            elseif ($action === 'desinstaller') {

                // Désinstallation d'un SA
                $demande = new Demande();
                $demande->setTypeDemande('desinstallation');
                $demande->setDateDemande(new \DateTime());
                $demande->setIdSalle($salle);
                $demande->setIdSa($salle->getSa());
                $demande->setStatut('En attente');
                $manager->persist($demande);
                $manager->flush();

                $this->addFlash('success', 'Demande de désinstallation créée !');
            }

            return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
        }

        return $this->render('info_salle/index.html.twig', [
            'controller_name' => 'InfoSalleController',
            'salle' => $salle,
            'demandeEnCours' => $demandeEnCours,
        ]);
    }
}