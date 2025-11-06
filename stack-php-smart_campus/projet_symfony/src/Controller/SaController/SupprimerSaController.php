<?php

namespace App\Controller\SaController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SupprimerSaController extends AbstractController
{
    #[Route('/supprimer-sa', name: 'app_supprimer_sa')]
    public function index(): Response
    {
        return $this->render('supprimer_sa/index.html.twig', [
            'controller_name' => 'SupprimerSaController',
        ]);
    }
}
