<?php

namespace App\Controller;

use App\Entity\Mascota;
use App\Repository\SolicitudRepository; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\MascotaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/mascotas', name: 'app_admin_mascotas')]
    public function gestionarMascotas(MascotaRepository $mascotaRepository, Request $request): Response
    {
        // 1. Capturar filtros
        $especie = $request->query->get('especie');
        $tamano = $request->query->get('tamano');
        $edad = $request->query->get('edad');
        $estado = $request->query->get('estado'); // "1" o "0"
        $orden = $request->query->get('orden');

        // 2. Buscar
        $mascotas = $mascotaRepository->buscarParaAdmin($especie, $tamano, $edad, $estado, $orden);

        return $this->render('admin/mascotas.html.twig', [
            'mascotas' => $mascotas,
            // Enviamos los filtros de vuelta para mantener los selects marcados
            'filtros' => [
                'especie' => $especie,
                'tamano' => $tamano,
                'edad' => $edad,
                'estado' => $estado,
                'orden' => $orden
            ]
        ]);
    }



    // --- 2. EDITAR MASCOTA ---
    #[Route('/mascotas/editar/{id}', name: 'app_admin_mascota_editar')]
    public function editar(Mascota $mascota, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Si enviaron el formulario con cambios
        if ($request->isMethod('POST')) {
            
            $mascota->setNombre($request->request->get('nombre'));
            $mascota->setEspecie($request->request->get('especie'));
            $mascota->setEdad($request->request->get('edad'));
            $mascota->setTamano($request->request->get('tamano')); // Cuidado con la Ñ en el name del HTML
            $mascota->setDescripcion($request->request->get('descripcion'));
            $mascota->setImagen($request->request->get('imagen')); // Por ahora URL texto

            // Manejo del Checkbox "Disponible"
            // Si el checkbox no está marcado, $request->get devuelve null
            $disponible = $request->request->get('disponible') === 'on'; 
            $mascota->setDisponible($disponible);

            $entityManager->flush();

            $this->addFlash('success', 'Mascota actualizada correctamente.');
            return $this->redirectToRoute('app_admin_mascotas');
        }

        return $this->render('admin/editar_mascota.html.twig', [
            'mascota' => $mascota,
        ]);
    }

    // --- 3. ELIMINAR MASCOTA (Con Validación RN3) ---
    #[Route('/mascotas/eliminar/{id}', name: 'app_admin_mascota_eliminar')]
    public function eliminar(Mascota $mascota, EntityManagerInterface $entityManager, SolicitudRepository $solicitudRepository): Response
    {
        // RN3: Verificar si tiene solicitudes activas (Pendiente o En Revisión)
        $solicitudesActivas = $solicitudRepository->count([
            'mascota' => $mascota,
            'estado' => ['Pendiente', 'En Revisión']
        ]);

        if ($solicitudesActivas > 0) {
            $this->addFlash('error', 'No se puede eliminar la mascota porque tiene solicitudes en proceso.');
            return $this->redirectToRoute('app_admin_mascotas');
        }

        // Si pasa la validación, borramos
        $entityManager->remove($mascota);
        $entityManager->flush();

        $this->addFlash('success', 'Mascota eliminada correctamente.');
        return $this->redirectToRoute('app_admin_mascotas');
    }

    // --- 4. AGREGAR MASCOTA (ALTA) ---
    #[Route('/mascotas/agregar', name: 'app_admin_mascota_agregar')]
    public function agregar(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Si enviaron el formulario (POST)
        if ($request->isMethod('POST')) {
            
            $mascota = new Mascota();
            
            // Seteamos los datos
            $mascota->setNombre($request->request->get('nombre'));
            $mascota->setEspecie($request->request->get('especie'));
            $mascota->setEdad((int)$request->request->get('edad'));
            $mascota->setTamano($request->request->get('tamano'));
            $mascota->setDescripcion($request->request->get('descripcion'));
            $mascota->setImagen($request->request->get('imagen'));

            // RN2: Por defecto, una mascota nueva nace Disponible
            // (A menos que quieras un checkbox en el alta, pero la regla dice automático)
            $mascota->setDisponible(true);

            // Persistimos (Esto es vital porque es un objeto NUEVO)
            $entityManager->persist($mascota);
            $entityManager->flush();

            $this->addFlash('success', 'Mascota agregada correctamente.');
            return $this->redirectToRoute('app_admin_mascotas');
        }

        return $this->render('admin/agregar_mascota.html.twig');
    }
}