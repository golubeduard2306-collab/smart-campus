<?php

namespace App\Controller\SalleController;

use App\Entity\Salle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AjouterSalleController extends AbstractController
{
    #[Route('/ajouter-salle', name: 'app_ajouter_salle')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $salle = new Salle();
            $salle->setNomSalle($request->request->get('nom_salle'));
            $salle->setEtage((int)$request->request->get('etage'));
            $salle->setNbFenetres((int)$request->request->get('nb_fenetres'));
            $salle->setDateCreation(new \DateTime());

            $em->persist($salle);
            $em->flush();

            $this->addFlash('success', 'La salle a été ajoutée avec succès !');

            return $this->redirectToRoute('app_ajouter_salle');
        }

        return $this->render('ajouter_salle/index.html.twig');
    }
}
