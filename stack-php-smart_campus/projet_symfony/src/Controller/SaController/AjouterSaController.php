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
            // Récupérer la quantité depuis le formulaire (par défaut 1)
            $quantite = (int) $request->request->get('quantite', 1);
            
            // Vérifier que la quantité est valide
            if ($quantite < 1 || $quantite > 100) {
                $this->addFlash('error', 'La quantité doit être entre 1 et 100.');
                return $this->redirectToRoute('app_ajouter_sa');
            }
            
            // Créer le nombre de SA demandé
            for ($i = 0; $i < $quantite; $i++) {
                $sa = new SystemeAcquisition();

                // Remplir les champs obligatoires
                $sa->setDateCreation(new \DateTime());
                $sa->setStatut('Inactif'); // ou autre valeur par défaut

                $em->persist($sa);
            }
            
            $em->flush();

            $message = $quantite > 1 
                ? "$quantite nouveaux SA ont été ajoutés à la base de données."
                : 'Un nouveau SA a été ajouté à la base de données.';
            
            $this->addFlash('success', $message);

            return $this->redirectToRoute('app_ajouter_sa');
        }

        return $this->render('ajouter_sa/index.html.twig');
    }
}