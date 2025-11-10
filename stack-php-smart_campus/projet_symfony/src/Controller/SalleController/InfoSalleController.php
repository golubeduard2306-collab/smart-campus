<?php


namespace App\Controller\SalleController;

use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InfoSalleController extends AbstractController
{
    #[Route('/info-salle/{salleId}', name: 'app_info_salle')]
    public function showInfoSalle(
        // Injecte la dépendance du Repository pour accéder à la base de données
        SalleRepository $salleRepository,
        // Récupère l'ID depuis l'URL
        int $salleId
    ): Response {

        // 1. Recherche de la Salle par son ID
        $salle = $salleRepository->find($salleId);

        // 2. Gestion de l'erreur 404 (si non trouvée)
        if (!$salle) {
            // Renvoie une réponse 404 Not Found
            throw $this->createNotFoundException('La salle demandée n\'existe pas.');
        }

        // 3. Rendu du template Twig avec l'objet Salle trouvé
        return $this->render('info_salle/index.html.twig', [
            'salle' => $salle,
        ]);
    }
}