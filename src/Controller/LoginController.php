<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    // Mantenemos la ruta estándar '/login' y el nombre 'app_login'
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // Obtener el error de login si lo hubo (ej: contraseña incorrecta)
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Obtener el último email que ingresó el usuario
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // Ruta para cerrar sesión
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Este método nunca se ejecuta, Symfony lo intercepta antes.
        throw new \LogicException('Este método será interceptado por la firewall de seguridad.');
    }
}