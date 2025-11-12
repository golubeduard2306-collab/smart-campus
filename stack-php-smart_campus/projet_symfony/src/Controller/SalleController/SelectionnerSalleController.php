<?php

namespace App\Controller\SalleController;

use App\Entity\Salle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SelectionnerSalleController extends AbstractController
{
    #[Route('/selectionner-salle', name: 'app_selectionner_salle')]
    public function index(EntityManagerInterface $em): Response
    {
        $salles = $em ->getRepository(Salle::class)->findAll();

        return $this->render('selectionner_salle/index.html.twig', [
            'salles' => $salles,
        ]);
    }
}
