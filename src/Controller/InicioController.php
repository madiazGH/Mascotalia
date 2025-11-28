<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InicioController extends AbstractController
{
    // 1. Definimos la ruta raíz '/'
    // 2. Le damos el nombre interno 'app_inicio' (en español)
    #[Route('/', name: 'app_inicio')]
    public function index(): Response
    {
        // Renderizamos la plantilla que me pasaste.
        // NOTA: Si renombraste la carpeta 'home' a 'inicio', cambia la línea de abajo.
        return $this->render('home/index.html.twig', [
            'controller_name' => 'InicioController',
        ]);
    }
}