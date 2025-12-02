<?php

namespace App\Manager;

use App\Entity\Mascota;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\SolicitudRepository;
use Exception;

class MascotaManager
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $params;
    private SolicitudRepository $solicitudRepository;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params, SolicitudRepository $solicitudRepository)
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->solicitudRepository = $solicitudRepository;
    }

    /**
     * Guarda una mascota ya sea para crear o editar 
     */
    public function guardar(Mascota $mascota, ?UploadedFile $foto): void
    {
        // si hay foto nueva se realiza el cambio
        if ($foto) {
            
            // se borra la vieja (si existe) para no dejar basura
            $fotoVieja = $mascota->getImagen();
            if ($fotoVieja) {
                $this->borrarArchivoFisico($fotoVieja);
            }

            // se sube la nueva
            $nuevaFoto = $this->subirFoto($foto);
            $mascota->setImagen($nuevaFoto);
        }

        //  si es nueva, disponible por defecto
        if ($mascota->getId() === null) {
            $mascota->setDisponible(true);
        }

        // persistir y guardar cambios
        $this->entityManager->persist($mascota);
        $this->entityManager->flush();
    }

    
    /**
     * Elimina una mascota y su foto.
     */
    public function eliminar(Mascota $mascota): void
    {
        // cuenta las solicitudes con estado "Pendiente o En Revision"
        $solicitudesActivas = $this->solicitudRepository->count([
            'mascota' => $mascota,
            'estado' => ['Pendiente', 'En Revisión']
        ]);

        if ($solicitudesActivas > 0) {
            // se lanza una excepción para detener el proceso
            throw new Exception('No se puede eliminar la mascota porque tiene solicitudes en proceso.');
        }

        // se borra la imagen
        $this->borrarArchivoFisico($mascota->getImagen());

        // se borra de la base de datos
        $this->entityManager->remove($mascota);
        $this->entityManager->flush();
    }

    /**
     *  metodo para borrar las imagenes
     */
    private function borrarArchivoFisico(?string $nombreArchivo): void
    {
        // verificamos que el nombre no sea nulo o vacío
        if ($nombreArchivo) {
            
            $directorio = $this->params->get('mascotas_directory');
            $rutaCompleta = $directorio . '/' . $nombreArchivo;

            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta); // si existe lo borra
            }
        }
    }

    /**
     * metodo para subir imagen
     */
    private function subirFoto(UploadedFile $file): string
    {
        $newFilename = uniqid() . '.' . $file->guessExtension();

        // mueve el archivo y le asigna el nuevo nombre unico
        try {
            $file->move(
                $this->params->get('mascotas_directory'),
                $newFilename
            );
        } catch (\Exception $e) { 
            throw new \Exception('Error al subir la imagen');
        }

        return $newFilename;
    }
}