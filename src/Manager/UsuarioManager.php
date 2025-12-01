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
     * REGISTRO (Usa todas las validaciones)
     */
    public function registrar(array $datos): void
    {
        // 1. Validar vacíos (Solo necesario en registro, en perfil ya vienen llenos)
        $this->validarCamposObligatorios($datos);

        // 2. Validar formatos (Nombre, Apellido, Ubicación, Teléfono)
        $this->validarFormatos($datos);

        // 3. Validar password (Obligatorio en registro)
        $this->validarPassword($datos['password'], $datos['password_repeat']);

        // 4. Validar duplicados
        if ($this->usuarioRepository->findOneBy(['email' => $datos['email']])) {
            throw new Exception('El correo electrónico ya está registrado.');
        }

        // --- CREACIÓN ---
        $usuario = new Usuario();
        $usuario->setEmail($datos['email']);
        $usuario->setDni($datos['dni']);
        
        // Seteo datos comunes (reutilizamos lógica si quisieramos, pero aquí es seteo directo)
        $this->setearDatosBasicos($usuario, $datos);

        try {
            if (!empty($datos['fechaNacimiento'])) {
                $usuario->setFechaNacimiento(new \DateTime($datos['fechaNacimiento']));
            }
        } catch (\Exception $e) { 
            throw new Exception('El formato de la fecha de nacimiento es inválido.');
        }

        // Encriptar y guardar
        $hashed = $this->passwordHasher->hashPassword($usuario, $datos['password']);
        $usuario->setContraseña($hashed);
        $usuario->setRol(['ROLE_USER']);

        $this->entityManager->persist($usuario);
        $this->entityManager->flush();
    }

    /**
     * ACTUALIZACIÓN (Reutiliza las validaciones de formato)
     */
    public function actualizar(Usuario $usuario, array $datos): void
    {
        // 1. Validar formatos (Reutilizamos el mismo método privado)
        $this->validarFormatos($datos);

        // 2. Actualizar datos básicos
        $this->setearDatosBasicos($usuario, $datos);

        // 3. Password (Solo si enviaron algo, reutilizamos validación)
        if (!empty($datos['password'])) {
            $this->validarPassword($datos['password'], $datos['password_repeat']);
            
            $hashed = $this->passwordHasher->hashPassword($usuario, $datos['password']);
            $usuario->setContraseña($hashed);
        }

        $this->entityManager->flush();
    }

    // =========================================================================
    // MÉTODOS PRIVADOS (Aquí está la lógica compartida)
    // =========================================================================

    private function validarCamposObligatorios(array $datos): void
    {
        if (empty($datos['nombre']) || empty($datos['apellido']) || empty($datos['dni']) || 
            empty($datos['email']) || empty($datos['password']) || empty($datos['provincia']) || 
            empty($datos['ciudad']) || empty($datos['direccion']) || empty($datos['telefono'])) {
            throw new Exception('Por favor, completá todos los campos obligatorios.');
        }
    }

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

        // Teléfono
        if (!ctype_digit($datos['telefono'])) {
            throw new Exception('El teléfono solo debe contener números.');
        }
    }

    private function validarPassword(string $password, string $repetir): void
    {
        // Complejidad
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
            throw new Exception('La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.');
        }

        // Coincidencia
        if ($password !== $repetir) {
            throw new Exception('Las contraseñas no coinciden.');
        }
    }

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