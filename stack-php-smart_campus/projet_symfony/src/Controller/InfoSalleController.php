<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InfoSalleController extends AbstractController
{
    #[Route('/infosalle', name: 'app_info_salle')]
    public function index(): Response
    {
        return $this->render('info_salle/index.html.twig', [
            'controller_name' => 'InfoSalleController',
        ]);
    }
}
