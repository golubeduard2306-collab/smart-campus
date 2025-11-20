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
    #[Route('/info-salle/{nomSalle}', name: 'app_info_salle', methods: ['GET', 'POST'])]
    public function index(
        string $nomSalle,
        SalleRepository $salleRepository,
        SystemeAcquisitionRepository $systemeAcquisitionRepository,
        Request $request,
        EntityManagerInterface $manager
    ): Response
    {
        // Récupération de la salle
        $salle = $salleRepository->findOneBy(['nom_salle' => $nomSalle]);
        
        if (!$salle) {
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

            // Supprimer la salle
            if ($action === 'supprimer') {
                // Vérifier qu'il n'y a pas de demande en cours
                if ($demandeEnCours) {
                    $this->addFlash('error', 'Impossible de supprimer la salle : une demande est en cours.');
                    return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
                }
                
                // Vérifier qu'aucun SA n'est installé
                if ($salle->getSaId() !== null) {
                    $this->addFlash('error', 'Impossible de supprimer la salle : un système d\'acquisition est installé.');
                    return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
                }
                
                // Supprimer toutes les demandes anciennes liées à cette salle
                foreach ($salle->getDemandes() as $demande) {
                    $manager->remove($demande);
                }
                
                // Supprimer la salle
                $manager->remove($salle);
                $manager->flush();
                
                return $this->redirectToRoute('app_selectionner_salle');
            }

            // Annuler une demande en cours
            if ($action === 'annuler' && $demandeEnCours) {
                $manager->remove($demandeEnCours);
                $manager->flush();
                
                $this->addFlash('success', 'Demande annulée avec succès.');
                return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
            }

            // Si une demande est déjà en cours, on ne peut pas en créer une nouvelle
            if ($demandeEnCours) {
                $this->addFlash('error', 'Une demande est déjà en cours pour cette salle.');
                return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
            }

            // Créer une demande d'installation
            if ($action === 'installer') {
                // Vérifier qu'aucun SA n'est déjà installé
                if ($salle->getSaId() !== null) {
                    $this->addFlash('error', 'Un système d\'acquisition est déjà installé dans cette salle.');
                    return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
                }

                // Chercher un SA inactif disponible
                $saInactif = $systemeAcquisitionRepository->findOneBy(['statut' => 'Inactif']);
                
                if (!$saInactif) {
                    $this->addFlash('error', 'Aucun système d\'acquisition inactif disponible.');
                    return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
                }

                $demande = new Demande();
                $demande->setTypeDemande('Installation');
                $demande->setDateDemande(new \DateTime());
                $demande->setStatut('En cours');
                $demande->setIdSalle($salle);
                $demande->setIdSa($saInactif);

                $manager->persist($demande);
                $manager->flush();

                $this->addFlash('success', 'Demande d\'installation créée avec succès.');
                return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
            }

            // Créer une demande de désinstallation
            if ($action === 'desinstaller') {
                // Vérifier qu'un SA est bien installé
                $saInstalle = $salle->getSaId();
                
                if ($saInstalle === null) {
                    $this->addFlash('error', 'Aucun système d\'acquisition n\'est installé dans cette salle.');
                    return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
                }

                $demande = new Demande();
                $demande->setTypeDemande('Désinstallation');
                $demande->setDateDemande(new \DateTime());
                $demande->setStatut('En cours');
                $demande->setIdSalle($salle);
                $demande->setIdSa($saInstalle);

                $manager->persist($demande);
                $manager->flush();

                $this->addFlash('success', 'Demande de désinstallation créée avec succès.');
                return $this->redirectToRoute('app_info_salle', ['nomSalle' => $nomSalle]);
            }
        }

        // Déterminer si un SA est installé en se basant sur la relation directe Salle->sa
        // ET vérifier son statut (un SA est considéré installé s'il est Actif)
        $saInstalle = null;
        if ($salle->getSaId() !== null && $salle->getSaId()->getStatut() === 'Actif') {
            $saInstalle = $salle->getSaId();
        }

        // Compter le nombre de SA disponibles (statut "Inactif")
        $nbSaDisponibles = $systemeAcquisitionRepository->count(['statut' => 'Inactif']);

        return $this->render('info_salle/index.html.twig', [
            'controller_name' => 'InfoSalleController',
            'salle' => $salle,
            'demandeEnCours' => $demandeEnCours,
            'saInstalle' => $saInstalle,
            'nbSaDisponibles' => $nbSaDisponibles,
        ]);
    }
}
