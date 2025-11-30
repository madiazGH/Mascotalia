<?php

namespace App\DataFixtures;

use App\Entity\Usuario;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsuarioFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Crear un USUARIO COMÚN (Cliente)
        $user = new Usuario();
        $user->setEmail('cliente@mascotalia.com');
        $user->setNombre('Juan');
        $user->setApellido('Pérez');
        $user->setRol(['ROLE_USER']);
        // Importante: No seteamos roles porque por defecto en la Entidad ya pusimos que agregue ROLE_USER
        
        // Encriptar password
        $password = $this->hasher->hashPassword($user, 'Cliente123');
        $user->setContraseña($password);
        
        $manager->persist($user);

        // 2. Crear un ADMINISTRADOR
        $admin = new Usuario();
        $admin->setEmail('admin@mascotalia.com');
        $admin->setNombre('Admin123');
        $admin->setApellido('Sistema');
        $admin->setRol(['ROLE_ADMIN']); // Aquí sí forzamos el rol
        
        $passwordAdmin = $this->hasher->hashPassword($admin, 'admin');
        $admin->setContraseña($passwordAdmin);

        $manager->persist($admin);

        $manager->flush();
    }
}