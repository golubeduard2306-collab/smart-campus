<?php

namespace App\Controller\SalleController;

use App\Entity\Salle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ModifierSalleController extends AbstractController
{
    #[Route('/modifier-salle/{nom_salle}', name: 'app_modifier_salle')]
    public function modifier(string $nom_salle, Request $request, EntityManagerInterface $em): Response
    {
        $salle = $em->getRepository(Salle::class)->findOneBy(['nom_salle' => $nom_salle]);

        if (!$salle) {
            throw $this->createNotFoundException("Salle '$nom_salle' introuvable !");
        }


        if ($request->isMethod('POST')) {
            $nouveauNom = $request->request->get('nom_salle');

            $salleExistante = $em->getRepository(Salle::class)->findOneBy(['nom_salle' => $nouveauNom]);

            if ($salleExistante && $salleExistante->getId() !== $salle->getId()) {
                $this->addFlash('error', 'Ce nom de salle est déjà utilisé !');

                return $this->render('modifier_salle/index.html.twig', [
                    'salle' => $salle,
                ]);
            }

            $salle->setNomSalle($nouveauNom);
            $salle->setEtage($request->request->get('etage'));
            $salle->setNbFenetres($request->request->get('nb_fenetres'));

            $em->flush();

            $this->addFlash('success', 'Salle modifiée avec succès !');

            return $this->redirectToRoute('app_modifier_salle', [
                'nom_salle' => $salle->getNomSalle(),
            ]);
        }

        return $this->render('modifier_salle/index.html.twig', [
            'salle' => $salle,
        ]);
    }
}