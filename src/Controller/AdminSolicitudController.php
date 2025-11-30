<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Mascota;
use App\Entity\Solicitud;
use App\Repository\MascotaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/solicitudes')]
class AdminSolicitudController extends AbstractController
{
    // PANTALLA 1: LISTA DE MASCOTAS SOLICITADAS
    #[Route('/', name: 'app_admin_solicitudes')]
    public function index(MascotaRepository $mascotaRepo, Request $request, PaginatorInterface $paginator): Response
    {
        $especie = $request->query->get('especie');
        $tamano = $request->query->get('tamano');
        $orden = $request->query->get('orden');

        // 1. Obtenemos la Query
        $query = $mascotaRepo->buscarSolicitadasConFiltros($especie, $tamano, $orden);

        // 2. Paginamos
        $mascotas = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Límite por página (ajústalo si quieres probar con menos)
        );

        return $this->render('admin_solicitud/index.html.twig', [
            'mascotas' => $mascotas,
            'filtros' => [
                'especie' => $especie, 
                'tamano' => $tamano, 
                'orden' => $orden
            ]
        ]);
    }

    // PANTALLA 2: VER SOLICITUDES DE UNA MASCOTA ESPECÍFICA
    #[Route('/ver/{id}', name: 'app_admin_ver_solicitudes')]
    public function verSolicitudes(Mascota $mascota, PaginatorInterface $paginator, Request $request): Response
    {
        // Paginamos la colección de solicitudes directamente
        // KnpPaginator es tan bueno que sabe paginar Arrays y Colecciones de Doctrine también
        $solicitudes = $paginator->paginate(
            $mascota->getSolicitudes(), // La lista completa
            $request->query->getInt('page', 1),
            10 // Ponemos 5 por página para que sea fácil probar la paginación
        );

        return $this->render('admin_solicitud/ver.html.twig', [
            'mascota' => $mascota,
            'solicitudes' => $solicitudes, // Pasamos la versión paginada
        ]);
    }

    // ACCIÓN: CAMBIAR ESTADO
    #[Route('/cambiar-estado/{id}', name: 'app_admin_cambiar_estado', methods: ['POST'])]
    public function cambiarEstado(Solicitud $solicitud, Request $request, EntityManagerInterface $em): Response
    {
        $nuevoEstado = $request->request->get('estado');
        
        // RN3: Resolución Única
        if ($nuevoEstado === 'Aceptada') {
            
            // 1. Aceptar esta solicitud
            $solicitud->setEstado('Aceptada');
            
            // 2. Rechazar automáticamente las demás solicitudes de esta mascota
            $otrasSolicitudes = $solicitud->getMascota()->getSolicitudes();
            foreach ($otrasSolicitudes as $otra) {
                if ($otra->getId() !== $solicitud->getId() && $otra->getEstado() !== 'Rechazada') {
                    $otra->setEstado('Rechazada');
                }
            }

            // 3. Marcar mascota como NO disponible
            $solicitud->getMascota()->setDisponible(false);

        } else {
            // Si es Rechazada o En Revisión, solo cambiamos el estado
            $solicitud->setEstado($nuevoEstado);
            
            // Si la rechazamos, nos aseguramos que la mascota siga disponible (por si acaso)
            if ($nuevoEstado === 'Rechazada') {
                $solicitud->getMascota()->setDisponible(true);
            }
        }

        $em->flush();
        $this->addFlash('success', 'Estado actualizado correctamente.');

        // Volver a la lista de solicitudes de esa mascota
        return $this->redirectToRoute('app_admin_ver_solicitudes', ['id' => $solicitud->getMascota()->getId()]);
    }
}
