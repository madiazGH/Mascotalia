<?php

namespace App\Manager;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Exception;

class UsuarioManager
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher,
        UsuarioRepository $usuarioRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Registro 
     */
    public function registrar(array $datos): void
    {
        // se validan campos vacvios
        $this->validarCamposObligatorios($datos);

        // se validan formatos
        $this->validarFormatos($datos);

        // se valida contraseña
        $this->validarPassword($datos['password'], $datos['password_repeat']);

        // 4se valida que el mail no este registrado ya
        if ($this->usuarioRepository->findOneBy(['email' => $datos['email']])) {
            throw new Exception('El correo electrónico ya está registrado.');
        }

        // crea la entidad 
        $usuario = new Usuario();
        $usuario->setEmail($datos['email']);
        $usuario->setDni($datos['dni']);
        
        // setea los datos comunes (datos que se reutilizan en mi perfil)
        $this->setearDatosBasicos($usuario, $datos);

        try {
            if (!empty($datos['fechaNacimiento'])) {
                $usuario->setFechaNacimiento(new \DateTime($datos['fechaNacimiento']));
            }
        } catch (\Exception $e) { 
            throw new Exception('El formato de la fecha de nacimiento es inválido.');
        }

        // encriptar y guardar
        $hashed = $this->passwordHasher->hashPassword($usuario, $datos['password']);
        $usuario->setContraseña($hashed);
        $usuario->setRol(['ROLE_USER']);

        // impactar en base de datos 
        $this->entityManager->persist($usuario);
        $this->entityManager->flush();
    }

    /**
     * Actualizar usuario
     */
    public function actualizar(Usuario $usuario, array $datos): void
    {
        // valida formatos
        $this->validarFormatos($datos);

        // actualiza datos basicos
        $this->setearDatosBasicos($usuario, $datos);

        // actualiza la contraseña si es que se ingreso
        if (!empty($datos['password'])) {
            $this->validarPassword($datos['password'], $datos['password_repeat']);
            
            $hashed = $this->passwordHasher->hashPassword($usuario, $datos['password']);
            $usuario->setContraseña($hashed);
        }

        $this->entityManager->flush();
    }

    

    //Valida que los campos no esten vacios 
    private function validarCamposObligatorios(array $datos): void
    {
        if (empty($datos['nombre']) || empty($datos['apellido']) || empty($datos['dni']) || 
            empty($datos['email']) || empty($datos['password']) || empty($datos['provincia']) || 
            empty($datos['ciudad']) || empty($datos['direccion']) || empty($datos['telefono'])) {
            throw new Exception('Por favor, completá todos los campos obligatorios.');
        }
    }

    //Valida los formatos 
    private function validarFormatos(array $datos): void
    {
        $soloLetras = "/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/";

        // Nombre y Apellido
        if (!preg_match($soloLetras, $datos['nombre']) || !preg_match($soloLetras, $datos['apellido'])) {
            throw new Exception('El Nombre y Apellido solo pueden contener letras.');
        }

        // Provincia y Ciudad
        if (!preg_match($soloLetras, $datos['provincia']) || !preg_match($soloLetras, $datos['ciudad'])) {
            throw new Exception('La Provincia y Ciudad solo pueden contener letras.');
        }

        // Telefono
        if (!ctype_digit($datos['telefono'])) {
            throw new Exception('El teléfono solo debe contener números.');
        }
    }

    // valida contraseña
    private function validarPassword(string $password, string $repetir): void
    {
        // complejidad
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
            throw new Exception('La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.');
        }

        // coincidencia
        if ($password !== $repetir) {
            throw new Exception('Las contraseñas no coinciden.');
        }
    }

    //setea los campos que se comparten en el registro y en el perfil
    private function setearDatosBasicos(Usuario $usuario, array $datos): void
    {
        $usuario->setNombre($datos['nombre']);
        $usuario->setApellido($datos['apellido']);
        $usuario->setProvincia($datos['provincia']);
        $usuario->setCiudad($datos['ciudad']);
        $usuario->setDireccion($datos['direccion']);
        $usuario->setTelefono($datos['telefono']);
    }
}