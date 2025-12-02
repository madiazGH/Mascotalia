<?php

namespace App\Controller;

use App\Entity\Mascota;
use App\Entity\Solicitud;
use App\Manager\SolicitudManager; 
use App\Repository\MascotaRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/solicitudes')]
class AdminSolicitudController extends AbstractController
{
    //Ver mascotas que tienen solicitudes 
    #[Route('/', name: 'app_admin_solicitudes')]
    public function verMascotasSolicitadas(MascotaRepository $mascotaRepo, Request $request, PaginatorInterface $paginator): Response
    {
        $especie = $request->query->get('especie');
        $tamano = $request->query->get('tamano');
        $orden = $request->query->get('orden');

        $query = $mascotaRepo->buscarSolicitadasConFiltros($especie, $tamano, $orden);
        $mascotas = $paginator->paginate($query, $request->query->getInt('page', 1), 10);

        return $this->render('admin_solicitud/index.html.twig', [
            'mascotas' => $mascotas,
            'filtros' => ['especie' => $especie, 'tamano' => $tamano, 'orden' => $orden]
        ]);
    }

    //Ver solicitudes de mascotas
    #[Route('/ver/{id}', name: 'app_admin_ver_solicitudes')]
    public function verSolicitudes(Mascota $mascota, PaginatorInterface $paginator, Request $request): Response
    {

        $solicitudes = $paginator->paginate(
            $mascota->getSolicitudes(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('admin_solicitud/ver.html.twig', [
            'mascota' => $mascota,
            'solicitudes' => $solicitudes,
        ]);
    }

    //Cambiar estado solicitudes 
    #[Route('/cambiar-estado/{id}', name: 'app_admin_cambiar_estado', methods: ['POST'])]
    public function cambiarEstado(Solicitud $solicitud, Request $request, SolicitudManager $solicitudManager): Response
    {
        $nuevoEstado = $request->request->get('estado');
        
        try {
            $solicitudManager->administrarEstado($solicitud, $nuevoEstado);
            $this->addFlash('success', 'Estado actualizado correctamente.');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_ver_solicitudes', ['id' => $solicitud->getMascota()->getId()]);
    }
}
