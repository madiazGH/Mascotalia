<?php

namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistroController extends AbstractController
{
    #[Route('/registro', name: 'app_register')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Si me están mandando datos (POST)...
        if ($request->isMethod('POST')) {
            
            // 1. Capturo todos los datos del formulario en variables para trabajar más cómodo
            $nombre = $request->request->get('nombre');
            $apellido = $request->request->get('apellido');
            $dni = $request->request->get('dni');
            $fechaNacimiento = $request->request->get('fechaNacimiento');
            $provincia = $request->request->get('provincia');
            $ciudad = $request->request->get('ciudad');
            $direccion = $request->request->get('direccion');
            $telefono = $request->request->get('telefono');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $passwordRepeat = $request->request->get('password_repeat');

            // 2. VALIDACIÓN OBLIGATORIA: ¿Hay algún campo vacío?
            // Reviso uno por uno. Si alguno está vacío, freno todo.
            if (empty($nombre) || empty($apellido) || empty($dni) || empty($fechaNacimiento) || 
                empty($provincia) || empty($ciudad) || empty($direccion) || empty($telefono) || 
                empty($email) || empty($password)) {
                
                // Aviso al usuario del error
                $this->addFlash('error', 'Por favor, completá todos los campos obligatorios.');
                
                // Lo devuelvo al formulario (no guardo nada)
                return $this->render('registro/index.html.twig');
            }

            // 3. VALIDACIÓN DE CONTRASEÑAS
            if ($password !== $passwordRepeat) {
                $this->addFlash('error', 'Las contraseñas no coinciden.');
                return $this->render('registro/index.html.twig');
            }

            // 4. VALIDACIÓN DE DUPLICADOS (Ya la teníamos)
            $usuarioExistente = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);
            
            if ($usuarioExistente) {
                $this->addFlash('error', 'El correo electrónico ya está registrado.');
                return $this->render('registro/index.html.twig');
            }

            // --- Si pasé todas las validaciones de arriba, recién ahora creo el usuario ---

            $usuario = new Usuario();
            
            $usuario->setEmail($email);
            $usuario->setNombre($nombre);
            $usuario->setApellido($apellido);
            $usuario->setDni($dni);
            $usuario->setProvincia($provincia);
            $usuario->setCiudad($ciudad);
            $usuario->setDireccion($direccion);
            $usuario->setTelefono($telefono);

            // Intento procesar la fecha
            try {
                $usuario->setFechaNacimiento(new \DateTime($fechaNacimiento)); 
            } catch (\Exception $e) {
                $this->addFlash('error', 'El formato de la fecha es incorrecto.');
                return $this->render('registro/index.html.twig');
            }

            // Encripto y asigno rol
            $hashedPassword = $passwordHasher->hashPassword($usuario, $password);
            $usuario->setContraseña($hashedPassword);
            $usuario->setRol(['ROLE_USER']);

            // Guardo en la base de datos
            $entityManager->persist($usuario);
            $entityManager->flush();

            // Éxito
            $this->addFlash('success', 'Cuenta creada exitosamente. ¡Ahora podés iniciar sesión!');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registro/index.html.twig');
    }
}