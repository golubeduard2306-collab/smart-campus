<?php

namespace App\Controller\SaController;

use App\Entity\SystemeAcquisition;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SupprimerSaController extends AbstractController
{
    #[Route('/supprimer-sa', name: 'app_supprimer_sa', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $id = $request->request->get('id');

            if ($id) {
                $sa = $em->getRepository(SystemeAcquisition::class)->find($id);

                if ($sa) {
                    // Vérifier si le SA est assigné à une salle
                    if ($sa->getSalle() !== null) {
                        $this->addFlash('error', 'Impossible de supprimer le SA #' . $id . ' : il est assigné à la salle "' . $sa->getSalle()->getNomSalle() . '".');
                    } else {
                        $em->remove($sa);
                        $em->flush();

                        $this->addFlash('success', 'Le SA #' . $id . ' a été supprimé de la base de données.');
                    }
                } else {
                    $this->addFlash('error', 'Le SA #' . $id . ' n\'existe pas dans la base de données.');
                }
            } else {
                $this->addFlash('error', 'Veuillez saisir un ID valide.');
            }

            return $this->redirectToRoute('app_supprimer_sa');
        }

        // Récupérer tous les SA pour affichage
        $sas = $em->getRepository(SystemeAcquisition::class)->findAll();

        return $this->render('supprimer_sa/index.html.twig', [
            'sas' => $sas,
        ]);
    }
}
