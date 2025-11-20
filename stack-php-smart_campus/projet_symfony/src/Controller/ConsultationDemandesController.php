<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConsultationDemandesController extends AbstractController
{
    #[Route('/consultation-demandes', name: 'app_consultation_demandes')]
    public function index(Request $request, DemandeRepository $demandeRepository): Response
    {
        // Récupérer le filtre depuis la requête (par défaut "tout")
        $filtre = $request->query->get('filtre', 'tout');
        
        // Récupérer les demandes non archivées (statut != 'Terminé')
        $queryBuilder = $demandeRepository->createQueryBuilder('d')
            ->where('d.statut != :statut')
            ->setParameter('statut', 'Terminé')
            ->orderBy('d.date_demande', 'ASC');
        
        // Appliquer le filtre si nécessaire
        if ($filtre === 'installation') {
            $queryBuilder->andWhere('d.type_demande = :type')
                ->setParameter('type', 'installation');
        } elseif ($filtre === 'désinstallation') {
            $queryBuilder->andWhere('d.type_demande = :type')
                ->setParameter('type', 'désinstallation');
        }
        
        $demandes = $queryBuilder->getQuery()->getResult();

        return $this->render('consultation_demandes/index.html.twig', [
            'demandes' => $demandes,
            'filtre' => $filtre,
        ]);
    }

    #[Route('/consultation-demandes/archiver/{id}', name: 'app_archiver_demande', methods: ['POST'])]
    public function archiver(int $id, EntityManagerInterface $em): Response
    {
        $demande = $em->getRepository(Demande::class)->find($id);
        
        if (!$demande) {
            $this->addFlash('error', 'Demande introuvable.');
            return $this->redirectToRoute('app_consultation_demandes');
        }

        if ($demande->getTypeDemande() == 'Installation') {
            // Marquer la demande comme terminée
            $demande->setStatut('Terminé');
            
            $sa = $demande->getIdSa();
            $salle = $demande->getIdSalle();
            
            if ($sa && $salle) {
                // Mettre le SA comme Actif
                $sa->setStatut('Actif');
                
                // Lier le SA à la salle (relation bidirectionnelle)
                $salle->setSaId($sa);
                $sa->setSalle($salle);
                
                $em->persist($sa);
                $em->persist($salle);
            }
            
            $em->persist($demande);
            $em->flush();
            
            $this->addFlash('success', 'La demande d\'installation a été confirmée avec succès.');
        }
        elseif ($demande->getTypeDemande() == 'Désinstallation') {
            // Marquer la demande comme terminée
            $demande->setStatut('Terminé');
            
            $sa = $demande->getIdSa();
            $salle = $demande->getIdSalle();
            
            if ($sa && $salle) {
                // Mettre le SA comme Inactif
                $sa->setStatut('Inactif');
                
                // Détacher le SA de la salle (relation bidirectionnelle)
                $salle->setSaId(null);
                $sa->setSalle(null);
                
                $em->persist($sa);
                $em->persist($salle);
            }
            
            $em->persist($demande);
            $em->flush();
            
            $this->addFlash('success', 'La demande de désinstallation a été confirmée avec succès.');
        }
        
        return $this->redirectToRoute('app_consultation_demandes');
    }
}
