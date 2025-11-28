<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SolicitudController extends AbstractController
{
    // Ruta en español, nombre estándar para la navbar
    #[Route('/mis-solicitudes', name: 'app_mis_solicitudes')]
    public function index(): Response
    {
        // SIMULACIÓN DE DATOS
        // Esto es temporal hasta que tengamos el Login real funcionando.
        $solicitudes = [
            [
                'mascota' => 'Chiquito',
                'fecha' => '12 / 05 / 2025',
                'estado' => 'Pendiente',
                'imagen' => 'https://images.unsplash.com/photo-1591160690555-5debfba289f0?w=500&auto=format&fit=crop&q=60'
            ],
            [
                'mascota' => 'Rex',
                'fecha' => '20 / 04 / 2025',
                'estado' => 'Rechazada',
                'imagen' => 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=500&auto=format&fit=crop&q=60'
            ],
            [
                'mascota' => 'Lola',
                'fecha' => '05 / 06 / 2025',
                'estado' => 'En Revisión',
                'imagen' => 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=500&auto=format&fit=crop&q=60'
            ]
        ];

        return $this->render('solicitud/index.html.twig', [
            'solicitudes' => $solicitudes,
        ]);
    }
}