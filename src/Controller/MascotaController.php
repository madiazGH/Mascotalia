<?php

namespace App\Controller;

use App\Entity\Mascota;
use App\Repository\MascotaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MascotaController extends AbstractController
{
    // --- LISTADO CON FILTROS ---
    #[Route('/mascotas', name: 'app_mascotas')]
    public function index(MascotaRepository $mascotaRepository, Request $request): Response
    {
        $especie = $request->query->get('especie');
        $tamano = $request->query->get('tamano');
        $edad = $request->query->get('edad');
        
        // 1. Capturamos el orden
        $orden = $request->query->get('orden');

        // 2. Lo pasamos al repositorio
        $mascotas = $mascotaRepository->buscarConFiltros($especie, $tamano, $edad, $orden);

        return $this->render('mascota/index.html.twig', [
            'mascotas' => $mascotas,
            'filtros' => [
                'especie' => $especie,
                'tamano' => $tamano,
                'edad' => $edad,
                'orden' => $orden // 3. Lo devolvemos a la vista
            ]
        ]);
    }

    // --- DETALLE DE MASCOTA ---
    #[Route('/mascota/detalle/{id}', name: 'app_mascota_detalle')]
    public function detalle(Mascota $mascota): Response
    {
        return $this->render('mascota/detalle.html.twig', [
            'mascota' => $mascota,
        ]);
    }
}