<?php

namespace App\Controller;

use App\Entity\Salle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConsultationDemandesController extends AbstractController
{
    #[Route('/consultation-demandes', name: 'app_consultation_demandes')]
    public function index(EntityManagerInterface $em): Response
    {
        $salles = $em ->getRepository(Salle::class)->findAll();

        return $this->render('consultation_demandes/index.html.twig', [
            'salles' => $salles,
        ]);
    }
}
