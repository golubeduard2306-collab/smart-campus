<?php

namespace App\Controller\SalleController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SelectionnerSalleController extends AbstractController
{
    #[Route('/selectionner-salle', name: 'app_selectionner_salle')]
    public function index(): Response
    {
        return $this->render('selectionner_salle/index.html.twig', [
            'controller_name' => 'SelectionnerSalleController',
        ]);
    }
}
