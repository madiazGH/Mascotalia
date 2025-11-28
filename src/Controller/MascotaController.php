<?php

namespace App\Controller;

use App\Repository\MascotaRepository; // Importante: Importar esto
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MascotaController extends AbstractController
{
    // Listado de mascotas
    #[Route('/mascotas', name: 'app_mascotas')]
    public function index(MascotaRepository $mascotaRepository): Response
    {
        // 1. Usamos el repositorio para buscar en la BD.
        // El método findBy nos permite filtrar.
        // Buscamos solo las que tengan disponible = true (1 en la BD)
        $mascotas = $mascotaRepository->findBy(['disponible' => true]);

        return $this->render('mascota/index.html.twig', [
            // 2. Pasamos la variable 'mascotas' a la vista
            'mascotas' => $mascotas,
        ]);
    }

    // Detalle de una mascota (Ya lo tenías, lo dejamos preparado para después)
    #[Route('/mascota/detalle', name: 'app_mascota_detalle')]
    public function detalle(): Response
    {
        // ... por ahora sigue con datos falsos, luego lo conectamos ...
        return $this->render('mascota/detalle.html.twig', [
             // ...
        ]);
    }
}