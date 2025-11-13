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
            $nomSalle = $request->request->get('nom_salle');
            
            // Vérifier si une salle avec ce nom existe déjà
            $salleExistante = $em->getRepository(Salle::class)->findOneBy(['nom_salle' => $nomSalle]);
            
            if ($salleExistante) {
                $this->addFlash('error', 'Une salle nommée "' . $nomSalle . '" existe déjà dans la base de données.');
                return $this->redirectToRoute('app_ajouter_salle');
            }
            
            $salle = new Salle();
            $salle->setNomSalle($nomSalle);
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
