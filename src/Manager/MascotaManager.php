<?php

namespace App\Manager;

use App\Entity\Mascota;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MascotaManager
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $params;

    // Inyecto los servicios que necesito: BD y Parámetros (para saber la ruta de uploads)
    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    /**
     * Guarda una mascota (Nueva o Editada) y maneja la foto si viene una.
     */
    public function guardar(Mascota $mascota, ?UploadedFile $foto): void
    {
        // 1. Si me mandaron una foto, la proceso
        if ($foto) {
            $nuevoNombre = $this->subirFoto($foto);
            $mascota->setImagen($nuevoNombre);
        }

        // 2. Si es una mascota nueva (no tiene ID), la pongo disponible por defecto (RN2)
        if ($mascota->getId() === null) {
            $mascota->setDisponible(true);
        }

        // 3. Guardo en la base de datos
        $this->entityManager->persist($mascota);
        $this->entityManager->flush();
    }

    
    /**
     * Elimina una mascota y su foto asociada del disco.
     */
    public function eliminar(Mascota $mascota): void
    {
        // 1. Obtener el nombre del archivo guardado en la BD
        $nombreArchivo = $mascota->getImagen();

        if ($nombreArchivo) {
            // Construir la ruta completa al archivo
            // Usamos el parámetro que definimos en services.yaml
            $directorio = $this->params->get('mascotas_directory');
            $rutaCompleta = $directorio . '/' . $nombreArchivo;

            // 2. Verificar si el archivo existe físicamente y borrarlo
            if (file_exists($rutaCompleta) && is_file($rutaCompleta)) {
                unlink($rutaCompleta); // Esta función elimina el archivo del disco
            }
        }

        // 3. Borrar la entidad de la base de datos
        // (Gracias al cascade=['remove'] que pusimos antes, también borrará las solicitudes)
        $this->entityManager->remove($mascota);
        $this->entityManager->flush();
    }

    /**
     * Lógica privada para mover el archivo
     */
    private function subirFoto(UploadedFile $file): string
    {
        // Genero nombre único
        $newFilename = uniqid() . '.' . $file->guessExtension();

        try {
            // Muevo el archivo a la carpeta configurada en services.yaml
            $file->move(
                $this->params->get('mascotas_directory'),
                $newFilename
            );
        } catch (\Exception $e) {
            // Si falla, lanzo error para que el controller se entere
            throw new \Exception('Error al subir la imagen');
        }

        return $newFilename;
    }
}