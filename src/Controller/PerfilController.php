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

#[IsGranted('ROLE_USER')]
class PerfilController extends AbstractController
{
    #[Route('/mi-perfil', name: 'app_perfil')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            
            // 1. CAPTURAR DATOS
            $nombre = $request->request->get('nombre');
            $apellido = $request->request->get('apellido');
            $provincia = $request->request->get('provincia');
            $ciudad = $request->request->get('ciudad');
            $direccion = $request->request->get('direccion');
            $telefono = $request->request->get('telefono');

            // 2. VALIDACIONES DE FORMATO
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

            // 3. ACTUALIZAR DATOS BÁSICOS (Usamos las variables ya validadas)
            $user->setNombre($nombre);
            $user->setApellido($apellido);
            $user->setProvincia($provincia);
            $user->setCiudad($ciudad);
            $user->setDireccion($direccion);
            $user->setTelefono($telefono);

            // 4. MANEJO DE CONTRASEÑA
            $newPassword = $request->request->get('password');
            $repeatPassword = $request->request->get('password_repeat');

            if (!empty($newPassword)) {
                
                // Validación de complejidad
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $newPassword)) {
                    $this->addFlash('error', 'La nueva contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.');
                    return $this->redirectToRoute('app_perfil');
                }
                
                // Validación de coincidencia
                if ($newPassword === $repeatPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setContraseña($hashedPassword);
                } else {
                    $this->addFlash('error', 'Las contraseñas nuevas no coinciden.');
                    return $this->redirectToRoute('app_perfil'); // Redirigir es mejor aquí
                }
            }

            // 5. GUARDAR
            $entityManager->flush();

            $this->addFlash('success', '¡Tus datos se actualizaron correctamente!');
            return $this->redirectToRoute('app_perfil');
        }

        return $this->render('perfil/index.html.twig');
    }
}