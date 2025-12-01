<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Manager\UsuarioManager; // <--- Importante
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class PerfilController extends AbstractController
{
    #[Route('/mi-perfil', name: 'app_perfil')]
    public function index(Request $request, UsuarioManager $usuarioManager): Response
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

            // 3. SETEAR DATOS BÁSICOS
            $user->setNombre($nombre);
            $user->setApellido($apellido);
            $user->setProvincia($provincia);
            $user->setCiudad($ciudad);
            $user->setDireccion($direccion);
            $user->setTelefono($telefono);

            // 4. PREPARAR CONTRASEÑA (Si hay)
            $newPassword = $request->request->get('password');
            $repeatPassword = $request->request->get('password_repeat');
            $passwordParaGuardar = null;

            if (!empty($newPassword)) {
                // Validaciones de pass
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $newPassword)) {
                    $this->addFlash('error', 'Contraseña débil.');
                    return $this->redirectToRoute('app_perfil');
                }
                if ($newPassword === $repeatPassword) {
                    $passwordParaGuardar = $newPassword;
                } else {
                    $this->addFlash('error', 'Las contraseñas nuevas no coinciden.');
                    return $this->redirectToRoute('app_perfil');
                }
            }

            // 5. USAR MANAGER PARA GUARDAR
            // Le pasamos el usuario con datos nuevos y la password plana (si hubo cambio)
            // El manager decidirá si encriptar o no.
            $usuarioManager->actualizar($user, $passwordParaGuardar);

            $this->addFlash('success', '¡Tus datos se actualizaron correctamente!');
            return $this->redirectToRoute('app_perfil');
        }

        return $this->render('perfil/index.html.twig');
    }
}