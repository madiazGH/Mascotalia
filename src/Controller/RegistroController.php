<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Manager\UsuarioManager; // <--- Importante
use App\Repository\UsuarioRepository; // Para chequear duplicados
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistroController extends AbstractController
{
    #[Route('/registro', name: 'app_register')]
    public function index(Request $request, UsuarioManager $usuarioManager, UsuarioRepository $usuarioRepo): Response
    {
        if ($request->isMethod('POST')) {
            
            // 1. CAPTURAR Y VALIDAR DATOS (Esto sigue igual, validación de entrada)
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

            // Validaciones de Vacío
            if (empty($nombre) || empty($apellido) || empty($dni) || empty($fechaNacimiento) || 
                empty($provincia) || empty($ciudad) || empty($direccion) || empty($telefono) || 
                empty($email) || empty($password)) {
                $this->addFlash('error', 'Por favor, completá todos los campos obligatorios.');
                return $this->render('registro/index.html.twig');
            }

            // Validaciones de Formato (Regex)
            $soloLetras = "/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/";
            if (!preg_match($soloLetras, $nombre) || !preg_match($soloLetras, $apellido)) {
                $this->addFlash('error', 'El Nombre y Apellido solo pueden contener letras.');
                return $this->render('registro/index.html.twig');
            }
            if (!preg_match($soloLetras, $provincia) || !preg_match($soloLetras, $ciudad)) {
                $this->addFlash('error', 'La Provincia y Ciudad solo pueden contener letras.');
                return $this->render('registro/index.html.twig');
            }
            if (!ctype_digit($telefono)) {
                $this->addFlash('error', 'El teléfono solo debe contener números.');
                return $this->render('registro/index.html.twig');
            }

            // Validación Password Complejidad
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
                $this->addFlash('error', 'La contraseña es muy débil (Mínimo 8 caracteres, 1 Mayúscula, 1 minúscula, 1 número).');
                return $this->render('registro/index.html.twig');
            }
            if ($password !== $passwordRepeat) {
                $this->addFlash('error', 'Las contraseñas no coinciden.');
                return $this->render('registro/index.html.twig');
            }

            // Validación Duplicados
            if ($usuarioRepo->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'El correo electrónico ya está registrado.');
                return $this->render('registro/index.html.twig');
            }

            // --- AQUÍ ENTRA EL MANAGER ---
            // Creamos el objeto y seteamos lo básico
            $usuario = new Usuario();
            $usuario->setEmail($email);
            $usuario->setNombre($nombre);
            $usuario->setApellido($apellido);
            $usuario->setDni($dni);
            $usuario->setProvincia($provincia);
            $usuario->setCiudad($ciudad);
            $usuario->setDireccion($direccion);
            $usuario->setTelefono($telefono);

            try {
                $usuario->setFechaNacimiento(new \DateTime($fechaNacimiento)); 
            } catch (\Exception $e) { /* ... */ }

            // Delegamos la encriptación y el guardado al Manager
            $usuarioManager->registrar($usuario, $password);

            $this->addFlash('success', 'Cuenta creada exitosamente. ¡Ahora podés iniciar sesión!');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registro/index.html.twig');
    }
}