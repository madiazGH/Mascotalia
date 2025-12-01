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
    #[Route('/mis-solicitudes', name: 'app_mis_solicitudes')]
    public function index(SolicitudRepository $solicitudRepository): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        $solicitudes = $solicitudRepository->findBy(['usuario' => $usuario], ['fechaEnvio' => 'DESC']);
        
        return $this->render('solicitud/index.html.twig', [
            'solicitudes' => $solicitudes,
        ]);
    }

    #[Route('/solicitar/{id}', name: 'app_solicitar_adopcion')]
    public function solicitar(Mascota $mascota, SolicitudManager $solicitudManager): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        try {
            // El Manager hace TODAS las validaciones (Admin, Límite, Duplicado) y guarda
            $solicitudManager->crearSolicitud($usuario, $mascota);
            
            $this->addFlash('success', '¡Solicitud enviada con éxito!');
            return $this->redirectToRoute('app_mis_solicitudes');

        } catch (\Exception $e) {
            // Si el Manager se queja, mostramos su mensaje
            $this->addFlash('error', $e->getMessage());
            
            // Si el error fue porque es Admin, lo mandamos a su panel
            if ($e->getMessage() === 'Los administradores no pueden solicitar adopciones.') {
                return $this->redirectToRoute('app_admin_mascotas');
            }

            return $this->redirectToRoute('app_mascota_detalle', ['id' => $mascota->getId()]);
        }
    }

    #[Route('/solicitud/cancelar/{id}', name: 'app_solicitud_cancelar')]
        public function cancelar(Solicitud $solicitud, SolicitudManager $solicitudManager): Response
        {
            /** @var Usuario $usuario */
            $usuario = $this->getUser();

            try {
                // Delegamos la lógica al Manager
                $solicitudManager->cancelarSolicitud($usuario, $solicitud);
                
                $this->addFlash('success', 'La solicitud ha sido cancelada correctamente.');

            } catch (\Exception $e) {
                // Si falla (porque no es suya o no está pendiente), mostramos el error
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('app_mis_solicitudes');
        }
}