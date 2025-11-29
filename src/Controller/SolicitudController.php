<?php

namespace App\Controller;

use App\Entity\Mascota;
use App\Entity\Solicitud;
use App\Entity\Usuario;
use App\Repository\SolicitudRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $solicitudes = $solicitudRepository->findBy(
            ['usuario' => $usuario], 
            ['fechaEnvio' => 'DESC']
        );

        return $this->render('solicitud/index.html.twig', [
            'solicitudes' => $solicitudes,
        ]);
    }

    // ESTA ES LA FUNCIÓN QUE TE FALTA O ESTÁ MAL ESCRITA
    #[Route('/solicitar/{id}', name: 'app_solicitar_adopcion')]
    public function solicitar(Mascota $mascota, EntityManagerInterface $entityManager, SolicitudRepository $solicitudRepository): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        // 1. Validar límite (RN3)
        $cantidadActivas = $solicitudRepository->count([
            'usuario' => $usuario,
            'estado' => ['Pendiente', 'En Revisión'] 
        ]);

        if ($cantidadActivas >= 3) {
            // Actualizamos el mensaje para que sea coherente
            $this->addFlash('error', 'Ya tienes 3 solicitudes en proceso (Pendientes o En Revisión).');
            return $this->redirectToRoute('app_mascota_detalle', ['id' => $mascota->getId()]);
        }

        // 2. Validar duplicado (RN2)
        $existe = $solicitudRepository->findOneBy([
            'usuario' => $usuario,
            'mascota' => $mascota,
        ]);

        if ($existe) {
            $this->addFlash('error', 'Ya enviaste una solicitud para esta mascota.');
            return $this->redirectToRoute('app_mascota_detalle', ['id' => $mascota->getId()]);
        }

        // 3. Crear Solicitud
        $solicitud = new Solicitud();
        $solicitud->setUsuario($usuario);
        $solicitud->setMascota($mascota);
        $solicitud->setFechaEnvio(new \DateTime());
        $solicitud->setEstado('Pendiente');

        $entityManager->persist($solicitud);
        $entityManager->flush();

        $this->addFlash('success', '¡Solicitud enviada con éxito!');

        return $this->redirectToRoute('app_mis_solicitudes');
    }

    #[Route('/solicitud/cancelar/{id}', name: 'app_solicitud_cancelar')]
    public function cancelar(Solicitud $solicitud, EntityManagerInterface $entityManager): Response
    {
        // 1. SEGURIDAD: Verificar que la solicitud pertenezca al usuario logueado
        if ($solicitud->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No puedes cancelar una solicitud que no es tuya.');
        }

        // 2. REGLA DE NEGOCIO: Solo se puede cancelar si está "Pendiente"
        if ($solicitud->getEstado() !== 'Pendiente') {
            $this->addFlash('error', 'No se puede cancelar la solicitud porque ya está en proceso de revisión.');
            return $this->redirectToRoute('app_mis_solicitudes');
        }

        // 3. Proceder a eliminar
        // (O podrías cambiar el estado a "Cancelada" si prefieres guardar historial, 
        // pero eliminarla es lo más común para limpiar la lista).
        $entityManager->remove($solicitud);
        $entityManager->flush();

        $this->addFlash('success', 'La solicitud ha sido cancelada correctamente.');

        return $this->redirectToRoute('app_mis_solicitudes');
    }
}