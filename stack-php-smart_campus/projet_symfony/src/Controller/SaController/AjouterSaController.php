<?php

namespace App\Controller\SaController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AjouterSaController extends AbstractController
{
    #[Route('/ajouter-sa', name: 'app_ajouter_sa')]
    public function index(): Response
    {
        return $this->render('ajouter_sa/index.html.twig', [
            'controller_name' => 'AjouterSaController',
        ]);
    }
}
