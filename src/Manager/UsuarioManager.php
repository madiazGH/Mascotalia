<?php

namespace App\Manager;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsuarioManager
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Registra un usuario nuevo con las configuraciones por defecto
     */
    public function registrar(Usuario $usuario, string $passwordPlana): void
    {
        // 1. Encripto la contraseña
        $hashed = $this->passwordHasher->hashPassword($usuario, $passwordPlana);
        $usuario->setContraseña($hashed);

        // 2. Asigno rol por defecto (Array)
        $usuario->setRol(['ROLE_USER']);

        // 3. Guardo
        $this->entityManager->persist($usuario);
        $this->entityManager->flush();
    }

    /**
     * Actualiza un usuario existente (Perfil)
     */
    public function actualizar(Usuario $usuario, ?string $nuevaPassword): void
    {
        // Solo si el usuario escribió una nueva contraseña, la encripto y actualizo
        if ($nuevaPassword) {
            $hashed = $this->passwordHasher->hashPassword($usuario, $nuevaPassword);
            $usuario->setContraseña($hashed);
        }

        // Guardo los cambios (nombre, apellido, etc. ya vienen seteados en el objeto)
        $this->entityManager->flush();
    }
}