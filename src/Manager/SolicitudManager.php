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
     * Crea una solicitud
     */
    public function crearSolicitud(Usuario $usuario, Mascota $mascota): void
    {
        // si el usuario es admin no le permite crear una solicitud
        if (in_array('ROLE_ADMIN', $usuario->getRoles())) {
            throw new Exception('Los administradores no pueden solicitar adopciones.');
        }

        // cuenta las solicitudes con estado "Pendiente o En Revision"
        $cantidadActivas = $this->solicitudRepository->count([
            'usuario' => $usuario,
            'estado' => ['Pendiente', 'En Revisión']
        ]);

        //si son mas de 3 te envia el mensaje 
        if ($cantidadActivas >= 3) {
            throw new Exception('Ya tienes 3 solicitudes en proceso (Pendientes o En Revisión).');
        }

        // busca una solicitud con el usuario y la mascota
        $existe = $this->solicitudRepository->findOneBy([
            'usuario' => $usuario,
            'mascota' => $mascota,
        ]);

        // si existe envia el mensaje de que ya existe la solicitud 
        if ($existe) {
            throw new Exception('Ya enviaste una solicitud para esta mascota.');
        }

        // se crea la solicitud
        $solicitud = new Solicitud();
        $solicitud->setUsuario($usuario);
        $solicitud->setMascota($mascota);
        $solicitud->setFechaEnvio(new \DateTime());
        $solicitud->setEstado('Pendiente');

        // se impacta en la base 
        $this->entityManager->persist($solicitud);
        $this->entityManager->flush();
    }
    
    /**
     *  Cancela la solicitud
     */
    public function cancelarSolicitud(Usuario $usuario, Solicitud $solicitud): void
    {
        // verificar que la solicitud pertenezca al usuario que intenta borrarla
        if ($solicitud->getUsuario() !== $usuario) {
            throw new Exception('No puedes cancelar una solicitud que no es tuya.');
        }

        // solo se puede cancelar si está "Pendiente"
        if ($solicitud->getEstado() !== 'Pendiente') {
            throw new Exception('No se puede cancelar la solicitud porque ya está en proceso de revisión o fue resuelta.');
        }

        // se elimina de la base de datos
        $this->entityManager->remove($solicitud);
        $this->entityManager->flush();
    }

    /**
     *  Gestiona el cambio de estado realizado por el administrador.
     */
    public function administrarEstado(Solicitud $solicitud, string $nuevoEstado): void
    {
        // validacion basica 
        if (!in_array($nuevoEstado, ['Pendiente', 'En Revisión', 'Aceptada', 'Rechazada'])) {
            throw new Exception('Estado no válido.');
        }

        // si el nuevo estado es "Aceptada"
        if ($nuevoEstado === 'Aceptada') {
            
            // se acepta
            $solicitud->setEstado('Aceptada');
            
            // buscan las otras solicitudes de la mascota
            $otrasSolicitudes = $solicitud->getMascota()->getSolicitudes();
            
            foreach ($otrasSolicitudes as $otra) {
                //todas las solicitudes que no sean la aceptada se rechazan si es que ya lo estan
                if ($otra->getId() !== $solicitud->getId() && $otra->getEstado() !== 'Rechazada') {
                    $otra->setEstado('Rechazada');
                }
            }

            // la mascota se pone como no disponible 
            $solicitud->getMascota()->setDisponible(false);

        } else {
            // En Revisión, Rechazada o vuelta a Pendiente
            $solicitud->setEstado($nuevoEstado);
            
            // Si se rechaza, aseguramos que la mascota vuelva a estar disponible 
            if ($nuevoEstado === 'Rechazada') {
                $solicitud->getMascota()->setDisponible(true);
            }
        }

        // Guardar todo
        $this->entityManager->flush();
    }
}