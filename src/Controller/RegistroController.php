<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistroController extends AbstractController
{
    // IMPORTANTE: El nombre 'app_register' debe coincidir con lo que busca la Navbar
    #[Route('/registro', name: 'app_register')]
    public function index(): Response
    {
        // Renderiza la plantilla que ya creamos antes
        return $this->render('registro/index.html.twig');
    }
}