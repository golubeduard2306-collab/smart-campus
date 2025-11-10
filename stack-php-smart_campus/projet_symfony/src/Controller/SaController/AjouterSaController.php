<?php

namespace App\Controller\SaController;

use App\Entity\SystemeAcquisition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AjouterSaController extends AbstractController
{
    #[Route('/ajouter-sa', name: 'app_ajouter_sa', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $sa = new SystemeAcquisition();

            // Remplir les champs obligatoires
            $sa->setDateCreation(new \DateTime());
            $sa->setStatut('Actif'); // ou autre valeur par défaut

            $em->persist($sa);
            $em->flush();

            $this->addFlash('success', 'Un nouveau SA a été ajouté à la base de données.');

            return $this->redirectToRoute('app_ajouter_sa');
        }

        return $this->render('ajouter_sa/index.html.twig');
    }
}