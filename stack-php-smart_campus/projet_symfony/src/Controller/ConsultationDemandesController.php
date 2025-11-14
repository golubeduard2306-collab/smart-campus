<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConsultationDemandesController extends AbstractController
{
    #[Route('/consultation-demandes', name: 'app_consultation_demandes')]
    public function index(): Response
    {
        return $this->render('consultation_demandes/index.html.twig', [
            'controller_name' => 'ConsultationDemandesController',
        ]);
    }
}
