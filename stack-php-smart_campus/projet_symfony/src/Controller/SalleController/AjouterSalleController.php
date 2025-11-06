<?php

namespace App\Controller\SalleController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AjouterSalleController extends AbstractController
{
    #[Route('/ajouter-salle', name: 'app_ajouter_salle')]
    public function index(): Response
    {
        return $this->render('ajouter_salle/index.html.twig', [
            'controller_name' => 'AjouterSalleController',
        ]);
    }
}
