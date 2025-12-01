<?php

namespace App\Controller;

use App\Manager\UsuarioManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistroController extends AbstractController
{
    #[Route('/registro', name: 'app_register')]
    public function index(Request $request, UsuarioManager $usuarioManager): Response
    {
        if ($request->isMethod('POST')) {
            
            $datos = $request->request->all();

            try {
                // 2. Le paso la "papa caliente" al Manager.
                // Él se encarga de validar, chequear duplicados y guardar.
                $usuarioManager->registrar($datos);

                // 3. Si no hubo excepciones, significa que todo salió bien.
                $this->addFlash('success', 'Cuenta creada exitosamente. ¡Ahora podés iniciar sesión!');
                return $this->redirectToRoute('app_login');

            } catch (\Exception $e) {
                // 4. Si el Manager se quejó (validación fallida), capturo el mensaje y lo muestro.
                // No guardo nada y vuelvo a mostrar el formulario.
                $this->addFlash('error', $e->getMessage());
                return $this->render('registro/index.html.twig');
            }
        }

        return $this->render('registro/index.html.twig');
    }
}