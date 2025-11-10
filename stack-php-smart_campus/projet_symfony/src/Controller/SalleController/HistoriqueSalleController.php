<?php

namespace App\Controller\SalleController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HistoriqueSalleController extends AbstractController
{
    #[Route('/historique-salle', name: 'app_historique_salle')]
    public function index(): Response
    {
        return $this->render('historique_salle/index.html.twig', [
            'controller_name' => 'HistoriqueSalleController',
        ]);
    }
}
