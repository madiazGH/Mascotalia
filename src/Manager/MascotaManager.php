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

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    /**
     * Guarda una mascota (Alta o Edición)
     */
    public function guardar(Mascota $mascota, ?UploadedFile $foto): void
    {
        // 1. Si hay foto nueva, procesamos el cambio
        if ($foto) {
            
            // A) Primero borramos la vieja (si existe) para no dejar basura
            $fotoVieja = $mascota->getImagen();
            if ($fotoVieja) {
                $this->borrarArchivoFisico($fotoVieja);
            }

            // B) Subimos la nueva
            $nuevoNombre = $this->subirFoto($foto);
            $mascota->setImagen($nuevoNombre);
        }

        // 2. Si es nueva, disponible por defecto
        if ($mascota->getId() === null) {
            $mascota->setDisponible(true);
        }

        // 3. Persistir
        $this->entityManager->persist($mascota);
        $this->entityManager->flush();
    }

    /**
     * Elimina mascota y su foto
     */
    public function eliminar(Mascota $mascota): void
    {
        // Borramos la foto antes de borrar el registro
        $this->borrarArchivoFisico($mascota->getImagen());

        $this->entityManager->remove($mascota);
        $this->entityManager->flush();
    }

    /**
     * Lógica privada para borrar archivos del disco
     */
    private function borrarArchivoFisico(?string $nombreArchivo): void
    {
        // Solo verificamos que el nombre no sea nulo o vacío
        if ($nombreArchivo) {
            
            $directorio = $this->params->get('mascotas_directory');
            $rutaCompleta = $directorio . '/' . $nombreArchivo;

            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta); // Borra el archivo
            }
        }
    }

    /**
     * Lógica privada para subir archivos
     */
    private function subirFoto(UploadedFile $file): string
    {
        $newFilename = uniqid() . '.' . $file->guessExtension();

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