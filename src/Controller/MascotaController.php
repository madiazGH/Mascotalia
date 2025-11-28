<?php

namespace App\Controller;

use App\Entity\Mascota; // <--- IMPORTANTE: Asegúrate de tener esta línea arriba
use App\Repository\MascotaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MascotaController extends AbstractController
{
    #[Route('/mascotas', name: 'app_mascotas')]
    public function index(MascotaRepository $mascotaRepository): Response
    {
        // Esto ya lo tenías bien, trae todas las disponibles
        $mascotas = $mascotaRepository->findBy(['disponible' => true]);

        return $this->render('mascota/index.html.twig', [
            'mascotas' => $mascotas,
        ]);
    }

    // CAMBIO IMPORTANTE AQUÍ:
    // 1. Agregamos {id} a la ruta.
    // 2. Inyectamos la entidad Mascota directamente.
    #[Route('/mascota/detalle/{id}', name: 'app_mascota_detalle')]
    public function detalle(Mascota $mascota): Response
    {
        // Ya no creamos el array falso $mascota = [...]
        // Symfony ya buscó en la BD al perro con ese ID y lo guardó en la variable $mascota
        
        return $this->render('mascota/detalle.html.twig', [
            'mascota' => $mascota,
        ]);
    }
}