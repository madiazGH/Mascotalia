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
        // 1. Creamos dos USUARIO COMÚN (Cliente)
        $usuario1 = new Usuario();
        $usuario1->setEmail('cliente1@mascotalia.com');
        $usuario1->setNombre('Juan');
        $usuario1->setApellido('Pérez');
        $usuario1->setDni('46448964');
        $usuario1->setFechaNacimiento(new \DateTime('2005-04-28'));
        $usuario1->setProvincia('Chaco');
        $usuario1->setCiudad('Resistencia');
        $usuario1->setDireccion('Saavedra 3153');
        $usuario1->setTelefono('342 463-3372');
        $usuario1->setRol(['ROLE_USER']);
        
        // Encriptar contraseña
        $password = $this->hasher->hashPassword($usuario1, 'Cliente123');
        $usuario1->setContraseña($password);
        
        $manager->persist($usuario1);

        $usuario2 = new Usuario();
        $usuario2->setEmail('cliente2@mascotalia.com');
        $usuario2->setNombre('Tomás');
        $usuario2->setApellido('González');
        $usuario2->setDni('46817263');
        $usuario2->setFechaNacimiento(new \DateTime('2004-02-13'));
        $usuario2->setProvincia('Santa Fe');
        $usuario2->setCiudad('Santa Fe');
        $usuario2->setDireccion('Belgrano 2374');
        $usuario2->setTelefono('342 517-4345');
        $usuario2->setRol(['ROLE_USER']);
        
        // Encriptar contraseña
        $password = $this->hasher->hashPassword($usuario2, 'Cliente123');
        $usuario2->setContraseña($password);
        
        $manager->persist($usuario2);

        // 2. Creamos dos ADMINISTRADORES
        $admin1 = new Usuario();
        $admin1->setEmail('admin1@mascotalia.com');
        $admin1->setNombre('Admin1');
        $admin1->setApellido('Sistema');
        $admin1->setRol(['ROLE_ADMIN']);
        
        $passwordAdmin = $this->hasher->hashPassword($admin1, 'Admin123');
        $admin1->setContraseña($passwordAdmin);

        $manager->persist($admin1);

        $admin2 = new Usuario();
        $admin2->setEmail('admin2@mascotalia.com');
        $admin2->setNombre('Admin2');
        $admin2->setApellido('Sistema');
        $admin2->setRol(['ROLE_ADMIN']);
        
        $passwordAdmin = $this->hasher->hashPassword($admin2, 'Admin123');
        $admin2->setContraseña($passwordAdmin);

        $manager->persist($admin2);

        $manager->flush();
    }
}