<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
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
    public function index(MascotaRepository $mascotaRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // 1. Obtener filtros (igual que antes)
        $especie = $request->query->get('especie');
        $tamano = $request->query->get('tamano');
        $edad = $request->query->get('edad');
        $orden = $request->query->get('orden');

        // 2. Obtener la Query (ahora devuelve Query, no array)
        $query = $mascotaRepository->buscarConFiltros($especie, $tamano, $edad, $orden);

        // 3. PAGINAR
        $mascotas = $paginator->paginate(
            $query, /* La consulta */
            $request->query->getInt('page', 1), /* Número de página actual (default 1) */
            8 /* Límite por página (8 se ve bien en grilla de 4x2) */
        );

        return $this->render('mascota/index.html.twig', [
            'mascotas' => $mascotas, // Pasamos el objeto paginación en vez del array
            'filtros' => [ 'especie' => $especie,
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