<?php

namespace App\Controller\SalleController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SupprimerSalleController extends AbstractController
{
    #[Route('/supprimer-salle', name: 'app_supprimer_salle')]
    public function index(): Response
    {
        return $this->render('supprimer_salle/index.html.twig', [
            'controller_name' => 'SupprimerSalleController',
        ]);
    }
}
