<?php

namespace App\Controller;

use App\Manager\UsuarioManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistroController extends AbstractController
{
    // Registrar usuario
    #[Route('/registro', name: 'app_register')]
    public function registrarUsuario(Request $request, UsuarioManager $usuarioManager): Response
    {
        if ($request->isMethod('POST')) {
            
            $datos = $request->request->all();

            try {
                
                // el manager se encarga de validar y registrar el usuario 
                $usuarioManager->registrar($datos);

                // si todo salio bien muestra el mensaje 
                $this->addFlash('success', 'Cuenta creada exitosamente. ¡Ahora podés iniciar sesión!');
                return $this->redirectToRoute('app_login');

            } catch (\Exception $e) {
                // si hubo algun problema muestra mensaje de error
                $this->addFlash('error', $e->getMessage());
                return $this->render('registro/index.html.twig');
            }
        }

        return $this->render('registro/index.html.twig');
    }
}