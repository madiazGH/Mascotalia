<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InicioController extends AbstractController
{
    // Pagina de inicio
    #[Route('/', name: 'app_inicio')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'InicioController',
        ]);
    }
}