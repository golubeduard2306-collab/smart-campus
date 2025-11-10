<?php

namespace App\Controller\SalleController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ModifierSalleController extends AbstractController
{
    #[Route('/modifier-salle', name: 'app_modifier_salle')]
    public function index(): Response
    {
        return $this->render('modifier_salle/index.html.twig', [
            'controller_name' => 'ModifierSalleController',
        ]);
    }
}
