<?php

namespace App\Controller\PageDeLiens;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageDeLiensController extends AbstractController
{
    #[Route('/', name: 'app_page_de_liens')]
    public function index(): Response
    {
        return $this->render('page_de_liens/index.html.twig', [
            'controller_name' => 'PageDeLiensController',
        ]);
    }
}
