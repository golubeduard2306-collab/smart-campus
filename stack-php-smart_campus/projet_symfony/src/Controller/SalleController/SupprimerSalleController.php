<?php

namespace App\Controller\SalleController;

use App\Entity\Salle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SupprimerSalleController extends AbstractController
{
    #[Route('/supprimer-salle', name: 'app_supprimer_salle', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $nomSalle = $request->request->get('nom_salle');

            if ($nomSalle) {
                $salle = $em->getRepository(Salle::class)->findOneBy(['nom_salle' => $nomSalle]);

                if ($salle) {
                    $idSalle = $salle->getId();
                    $em->remove($salle);
                    $em->flush();

                    $this->addFlash('success', 'La salle "' . $nomSalle . '" (ID: ' . $idSalle . ') a été supprimée avec succès.');
                } else {
                    $this->addFlash('error', 'La salle "' . $nomSalle . '" n\'existe pas dans la base de données.');
                }
            } else {
                $this->addFlash('error', 'Veuillez saisir un nom de salle valide.');
            }

            return $this->redirectToRoute('app_supprimer_salle');
        }

        return $this->render('supprimer_salle/index.html.twig');
    }
}
