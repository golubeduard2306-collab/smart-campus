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
                    $em->remove($sa);
                    $em->flush();

                    $this->addFlash('success', 'Le SA #' . $id . ' a été supprimé de la base de données.');
                } else {
                    $this->addFlash('error', 'Le SA #' . $id . ' n\'existe pas dans la base de données.');
                }
            } else {
                $this->addFlash('error', 'Veuillez saisir un ID valide.');
            }

            return $this->redirectToRoute('app_supprimer_sa');
        }

        return $this->render('supprimer_sa/index.html.twig');
    }
}
