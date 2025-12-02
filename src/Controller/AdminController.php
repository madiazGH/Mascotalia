<?php

namespace App\Controller;

use App\Entity\Mascota;
use App\Manager\MascotaManager; 
use App\Repository\MascotaRepository;
use App\Repository\SolicitudRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    // Lista de mascotas (admin)
    #[Route('/mascotas', name: 'app_admin_mascotas')]
    public function gestionarMascotas(MascotaRepository $mascotaRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $especie = $request->query->get('especie');
        $tamano = $request->query->get('tamano');
        $edad = $request->query->get('edad');
        $estado = $request->query->get('estado');
        $orden = $request->query->get('orden');

        $query = $mascotaRepository->buscarParaAdmin($especie, $tamano, $edad, $estado, $orden);

        $mascotas = $paginator->paginate($query, $request->query->getInt('page', 1), 10); //Pagina los resultados 

        return $this->render('admin/mascotas.html.twig', [
            'mascotas' => $mascotas,
            'filtros' => ['especie' => $especie, 'tamano' => $tamano, 'edad' => $edad, 'estado' => $estado, 'orden' => $orden]
        ]);
    }

    // Agregar Mascota 
    #[Route('/mascotas/agregar', name: 'app_admin_mascota_agregar')]
    public function agregar(Request $request, MascotaManager $mascotaManager): Response
    {
        if ($request->isMethod('POST')) {
            $mascota = new Mascota();
            
            // Seteamos datos básicos
            $mascota->setNombre($request->request->get('nombre'));
            $mascota->setEspecie($request->request->get('especie'));
            $mascota->setEdad($request->request->get('edad'));
            $mascota->setTamano($request->request->get('tamano'));
            $mascota->setDescripcion($request->request->get('descripcion'));

            // Obtenemos el archivo de la imagen
            $archivo = $request->files->get('imagen');

            try {
                // El managaer guarda la mascota y el archivo de la imagen
                $mascotaManager->guardar($mascota, $archivo);
                $this->addFlash('success', 'Mascota agregada correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('app_admin_mascotas');
        }
        return $this->render('admin/agregar_mascota.html.twig');
    }

    // Editar Mascota
    #[Route('/mascotas/editar/{id}', name: 'app_admin_mascota_editar')]
    public function editar(Mascota $mascota, Request $request, MascotaManager $mascotaManager): Response
    {
        if ($request->isMethod('POST')) {
            
            $mascota->setNombre($request->request->get('nombre'));
            $mascota->setEspecie($request->request->get('especie'));
            $mascota->setEdad($request->request->get('edad'));
            $mascota->setTamano($request->request->get('tamano'));
            $mascota->setDescripcion($request->request->get('descripcion'));
            
            // Checkbox disponible
            $disponible = $request->request->get('disponible') === 'on'; 
            $mascota->setDisponible($disponible);

            // Archivo (puede ser null si no se sube nada)
            $archivo = $request->files->get('imagen');

            try {
                //Se guarda y actualiza la mascota
                $mascotaManager->guardar($mascota, $archivo);
                $this->addFlash('success', 'Mascota actualizada correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('app_admin_mascotas');
        }

        return $this->render('admin/editar_mascota.html.twig', [
            'mascota' => $mascota,
        ]);
    }

    #[Route('/mascotas/eliminar/{id}', name: 'app_admin_mascota_eliminar')]
    public function eliminar(Mascota $mascota, MascotaManager $mascotaManager): Response
    {
        try {
            // Delegamos todo al Manager
            $mascotaManager->eliminar($mascota);
            
            // Si no explotó, éxito
            $this->addFlash('success', 'Mascota eliminada correctamente.');

        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_mascotas');
    }
}