<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile; 
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
    public function gestionarMascotas(MascotaRepository $mascotaRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // 1. Capturar filtros (Igual)
        $especie = $request->query->get('especie');
        $tamano = $request->query->get('tamano');
        $edad = $request->query->get('edad');
        $estado = $request->query->get('estado');
        $orden = $request->query->get('orden');

        // 2. Obtener Query
        $query = $mascotaRepository->buscarParaAdmin($especie, $tamano, $edad, $estado, $orden);

        // 3. PAGINAR
        $mascotas = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Para admin, listas de 10 o 20 suelen ser mejores
        );

        return $this->render('admin/mascotas.html.twig', [
            'mascotas' => $mascotas,
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
        if ($request->isMethod('POST')) {
            
            $mascota->setNombre($request->request->get('nombre'));
            $mascota->setEspecie($request->request->get('especie'));
            $mascota->setEdad($request->request->get('edad'));
            $mascota->setTamano($request->request->get('tamano'));
            $mascota->setDescripcion($request->request->get('descripcion'));
            
            // MANEJO DE IMAGEN (Solo si subieron una nueva)
            /** @var UploadedFile $archivo */
            $archivo = $request->files->get('imagen');
            
            if ($archivo) {
                // Si suben foto nueva, la procesamos y reemplazamos la vieja
                $nombreArchivo = $this->subirImagen($archivo);
                $mascota->setImagen($nombreArchivo);
            }
            // Si NO suben foto, no hacemos nada (se mantiene la que ya tenía)

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

    // --- 4. AGREGAR MASCOTA ---
    #[Route('/mascotas/agregar', name: 'app_admin_mascota_agregar')]
    public function agregar(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $mascota = new Mascota();
            
            // Datos normales
            $mascota->setNombre($request->request->get('nombre'));
            $mascota->setEspecie($request->request->get('especie'));
            $mascota->setEdad($request->request->get('edad'));
            $mascota->setTamano($request->request->get('tamano'));
            $mascota->setDescripcion($request->request->get('descripcion'));
            $mascota->setDisponible(true);

            // MANEJO DE IMAGEN (Archivo)
            /** @var UploadedFile $archivo */
            $archivo = $request->files->get('imagen'); // Usamos 'files' en vez de 'request'
            
            if ($archivo) {
                $nombreArchivo = $this->subirImagen($archivo);
                $mascota->setImagen($nombreArchivo); // Guardamos "perro123.jpg" en la BD
            }

            $entityManager->persist($mascota);
            $entityManager->flush();

            $this->addFlash('success', 'Mascota agregada correctamente.');
            return $this->redirectToRoute('app_admin_mascotas');
        }
        return $this->render('admin/agregar_mascota.html.twig');
    }

    // --- FUNCIÓN PRIVADA PARA SUBIR IMÁGENES ---
    // Esta función hace la magia de mover el archivo y renombrarlo
    private function subirImagen(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // Generamos un nombre único para evitar duplicados (ej: "fido-65a4b3c.jpg")
        $newFilename = uniqid().'.'.$file->guessExtension();

        try {
            $file->move(
                $this->getParameter('mascotas_directory'), // Usamos el parámetro de services.yaml
                $newFilename
            );
        } catch (\Exception $e) {
            // Manejar error si no se puede mover
            throw new \Exception('Error al subir la imagen');
        }

        return $newFilename;
    }
}