<?php

namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')] // Solo usuarios logueados pueden entrar acá
class PerfilController extends AbstractController
{
    #[Route('/mi-perfil', name: 'app_perfil')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var Usuario $user */
        $user = $this->getUser(); // Obtenemos el usuario que está logueado

        // Recibimos los datos
            $nombre = $request->request->get('nombre');
            $apellido = $request->request->get('apellido');
            $provincia = $request->request->get('provincia');
            $ciudad = $request->request->get('ciudad');
            $direccion = $request->request->get('direccion'); // Este permite números y letras, no lo validamos
            $telefono = $request->request->get('telefono');

            // --- VALIDACIONES DE FORMATO ---
            $soloLetras = "/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/";

            if (!preg_match($soloLetras, $nombre) || !preg_match($soloLetras, $apellido)) {
                $this->addFlash('error', 'El Nombre y Apellido solo pueden contener letras.');
                return $this->redirectToRoute('app_perfil');
            }

            if (!preg_match($soloLetras, $provincia) || !preg_match($soloLetras, $ciudad)) {
                $this->addFlash('error', 'La Provincia y Ciudad solo pueden contener letras.');
                return $this->redirectToRoute('app_perfil');
            }

            if (!ctype_digit($telefono)) {
                $this->addFlash('error', 'El teléfono solo debe contener números.');
                return $this->redirectToRoute('app_perfil');
            }
            // -------------------------------

        // Si envían el formulario (POST)
        if ($request->isMethod('POST')) {
            
            // 1. Actualizamos datos básicos
            $user->setNombre($request->request->get('nombre'));
            $user->setApellido($request->request->get('apellido'));
            $user->setProvincia($request->request->get('provincia'));
            $user->setCiudad($request->request->get('ciudad'));
            $user->setDireccion($request->request->get('direccion'));
            $user->setTelefono($request->request->get('telefono'));
            

            // 3. Manejo de Contraseña (SOLO si el usuario escribió algo)
            $newPassword = $request->request->get('password');
            $repeatPassword = $request->request->get('password_repeat');

            if (!empty($newPassword)) {

                // --- AGREGAMOS LA VALIDACIÓN AQUÍ TAMBIÉN ---
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $newPassword)) {
                    $this->addFlash('error', 'La nueva contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.');
                    // IMPORTANTE: Redirigimos al mismo lugar para no guardar nada
                    return $this->redirectToRoute('app_perfil');
                }
                
                if ($newPassword === $repeatPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setContraseña($hashedPassword);
                } else {
                    $this->addFlash('error', 'Las contraseñas nuevas no coinciden.');
                    return $this->render('perfil/index.html.twig');
                }
            }

            // 4. Guardamos cambios
            // No hace falta 'persist' porque el usuario ya existe, solo 'flush'
            $entityManager->flush();

            $this->addFlash('success', '¡Tus datos se actualizaron correctamente!');
            
            // Recargamos la misma página para ver los cambios
            return $this->redirectToRoute('app_perfil');
        }

        return $this->render('perfil/index.html.twig');
    }
}