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
    // Lista de mascotas
    #[Route('/mascotas', name: 'app_mascotas')]
    public function listarMascotas(MascotaRepository $mascotaRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // Obtener filtros (puede no haber)
        $especie = $request->query->get('especie');
        $tamano = $request->query->get('tamano');
        $edad = $request->query->get('edad');
        $orden = $request->query->get('orden');

        
        $query = $mascotaRepository->buscarConFiltros($especie, $tamano, $edad, $orden);

        $mascotas = $paginator->paginate(
            $query, 
            $request->query->getInt('page', 1), 
            8 
        );

        return $this->render('mascota/index.html.twig', [
            'mascotas' => $mascotas, 
            'filtros' => [ 'especie' => $especie,
                'tamano' => $tamano,
                'edad' => $edad,
                'orden' => $orden 
            ] 
            ]);
    }

    // Detalle de la mascota
    #[Route('/mascota/detalle/{id}', name: 'app_mascota_detalle')]
    public function verDetalle(Mascota $mascota): Response
    {
        return $this->render('mascota/detalle.html.twig', [
            'mascota' => $mascota,
        ]);
    }
}