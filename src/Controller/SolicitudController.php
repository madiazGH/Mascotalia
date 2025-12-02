<?php

namespace App\Controller;

use App\Entity\Mascota;
use App\Entity\Usuario;
use App\Entity\Solicitud;
use App\Manager\SolicitudManager; // <--- Importante
use App\Repository\SolicitudRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class SolicitudController extends AbstractController
{
    // Ver solicitudes del usuario    
    #[Route('/mis-solicitudes', name: 'app_mis_solicitudes')]
    public function verSolicitudes(SolicitudRepository $solicitudRepository): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        $solicitudes = $solicitudRepository->findBy(['usuario' => $usuario], ['fechaEnvio' => 'DESC']);
        
        return $this->render('solicitud/index.html.twig', [
            'solicitudes' => $solicitudes,
        ]);
    }

    // Solicitar mascota 
    #[Route('/solicitar/{id}', name: 'app_solicitar_adopcion')]
    public function solicitarMascota(Mascota $mascota, SolicitudManager $solicitudManager): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        try {
            // El manager valida las reglas y crea la solicitud
            $solicitudManager->crearSolicitud($usuario, $mascota);
            //Si todo sale bien se muestra el mensaje
            $this->addFlash('success', '¡Solicitud enviada con éxito!');
            return $this->redirectToRoute('app_mis_solicitudes');

        } catch (\Exception $e) {
            // si hay algun problema se muestra el mensaje
            $this->addFlash('error', $e->getMessage());
            
            // si el error fue porque es Admin, lo mandamos a su panel (solo se da en algunas situaciones)
            if ($e->getMessage() === 'Los administradores no pueden solicitar adopciones.') {
                return $this->redirectToRoute('app_admin_mascotas');
            }

            return $this->redirectToRoute('app_mascota_detalle', ['id' => $mascota->getId()]);
        }
    }

    //Cancelar solicitud de mascota
    #[Route('/solicitud/cancelar/{id}', name: 'app_solicitud_cancelar')]
        public function cancelarSolicitud(Solicitud $solicitud, SolicitudManager $solicitudManager): Response
        {
            /** @var Usuario $usuario */
            $usuario = $this->getUser();

            try {
                // el manager valida y cancela la solicitud
                $solicitudManager->cancelarSolicitud($usuario, $solicitud);
                // si sale todo bien muestra el mensaje
                $this->addFlash('success', 'La solicitud ha sido cancelada correctamente.');

            } catch (\Exception $e) {
                // si hay algun error muestra el mensaje
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('app_mis_solicitudes');
        }
}