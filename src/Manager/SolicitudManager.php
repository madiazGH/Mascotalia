<?php

namespace App\Manager;

use App\Entity\Mascota;
use App\Entity\Solicitud;
use App\Entity\Usuario;
use App\Repository\SolicitudRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class SolicitudManager
{
    private SolicitudRepository $solicitudRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(SolicitudRepository $solicitudRepository, EntityManagerInterface $entityManager)
    {
        $this->solicitudRepository = $solicitudRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Intenta crear una solicitud. Lanza Exception si viola alguna regla.
     */
    public function crearSolicitud(Usuario $usuario, Mascota $mascota): void
    {
        // 1. VALIDACIÓN: Bloquear Admin
        if (in_array('ROLE_ADMIN', $usuario->getRoles())) {
            throw new Exception('Los administradores no pueden solicitar adopciones.');
        }

        // 2. VALIDACIÓN RN3: Límite de pendientes
        $cantidadActivas = $this->solicitudRepository->count([
            'usuario' => $usuario,
            'estado' => ['Pendiente', 'En Revisión']
        ]);

        if ($cantidadActivas >= 3) {
            throw new Exception('Ya tienes 3 solicitudes en proceso (Pendientes o En Revisión).');
        }

        // 3. VALIDACIÓN RN2: Duplicados
        $existe = $this->solicitudRepository->findOneBy([
            'usuario' => $usuario,
            'mascota' => $mascota,
        ]);

        if ($existe) {
            throw new Exception('Ya enviaste una solicitud para esta mascota.');
        }

        // 4. CREACIÓN
        $solicitud = new Solicitud();
        $solicitud->setUsuario($usuario);
        $solicitud->setMascota($mascota);
        $solicitud->setFechaEnvio(new \DateTime());
        $solicitud->setEstado('Pendiente');

        $this->entityManager->persist($solicitud);
        $this->entityManager->flush();
    }
    
    // Aquí podrías agregar métodos como 'cancelarSolicitud' o 'aceptarSolicitud' en el futuro
}