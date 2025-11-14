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

            $nomSalle = strtoupper($request->request->get('nom_salle'));
            $etage = (int)$request->request->get('etage');

            // Vérification du format
            if (!preg_match('/^[DC]' . $etage . '\d{2}$/', $nomSalle)) {
                $this->addFlash('error',
                    'Le nom de la salle doit commencer par D ou C, 
                    être suivi de l\'étage (' . $etage . '), 
                    puis de deux chiffres. Exemple : D' . $etage . '01'
                );
                return $this->redirectToRoute('app_ajouter_salle');
            }

            // Vérifier si une salle avec ce nom existe déjà
            $salleExistante = $em->getRepository(Salle::class)->findOneBy(['nom_salle' => $nomSalle]);

            if ($salleExistante) {
                $this->addFlash('error', 'Une salle nommée "' . $nomSalle . '" existe déjà.');
                return $this->redirectToRoute('app_ajouter_salle');
            }

            $salle = new Salle();
            $salle->setNomSalle($nomSalle);
            $salle->setEtage($etage);
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
