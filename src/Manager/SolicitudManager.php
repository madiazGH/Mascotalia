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
    
    /**
     * Cancela (elimina) una solicitud validando reglas de negocio.
     */
    public function cancelarSolicitud(Usuario $usuario, Solicitud $solicitud): void
    {
        // 1. SEGURIDAD: Verificar que la solicitud pertenezca al usuario que intenta borrarla
        if ($solicitud->getUsuario() !== $usuario) {
            throw new Exception('No puedes cancelar una solicitud que no es tuya.');
        }

        // 2. REGLA DE NEGOCIO: Solo se puede cancelar si está "Pendiente"
        if ($solicitud->getEstado() !== 'Pendiente') {
            throw new Exception('No se puede cancelar la solicitud porque ya está en proceso de revisión o fue resuelta.');
        }

        // 3. ELIMINACIÓN
        $this->entityManager->remove($solicitud);
        $this->entityManager->flush();
    }

    /**
     * Gestiona el cambio de estado realizado por el administrador.
     * Aplica la regla RN3: Si se acepta una, se rechazan las competidoras.
     */
    public function administrarEstado(Solicitud $solicitud, string $nuevoEstado): void
    {
        // 1. Validaciones básicas
        if (!in_array($nuevoEstado, ['Pendiente', 'En Revisión', 'Aceptada', 'Rechazada'])) {
            throw new Exception('Estado no válido.');
        }

        // 2. Lógica RN3: Resolución Única
        if ($nuevoEstado === 'Aceptada') {
            
            // Aceptar la actual
            $solicitud->setEstado('Aceptada');
            
            // Buscar y rechazar a la competencia
            $otrasSolicitudes = $solicitud->getMascota()->getSolicitudes();
            
            foreach ($otrasSolicitudes as $otra) {
                // Si no es la misma y no estaba ya rechazada
                if ($otra->getId() !== $solicitud->getId() && $otra->getEstado() !== 'Rechazada') {
                    $otra->setEstado('Rechazada');
                }
            }

            // Marcar mascota como NO disponible (Ya tiene dueño)
            $solicitud->getMascota()->setDisponible(false);

        } else {
            // Caso: En Revisión, Rechazada o vuelta a Pendiente
            $solicitud->setEstado($nuevoEstado);
            
            // Si se rechaza, aseguramos que la mascota vuelva a estar disponible (por si acaso)
            if ($nuevoEstado === 'Rechazada') {
                $solicitud->getMascota()->setDisponible(true);
            }
        }

        // 3. Guardar todo
        $this->entityManager->flush();
    }
}