<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Manager\UsuarioManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class PerfilController extends AbstractController
{
    // Ver formulario con datos del usuario 
    #[Route('/mi-perfil', name: 'app_perfil')]
    public function verPerfil(Request $request, UsuarioManager $usuarioManager): Response
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        // Si se clickea el boton "Guardar Cambios"
        if ($request->isMethod('POST')) {
            
            // Recupero datos del usuario
            $datos = $request->request->all();

            try {
                // se actualizan los datos del usuario con los datos recuperados
                $usuarioManager->actualizar($user, $datos);

                $this->addFlash('success', '¡Tus datos se actualizaron correctamente!');

            } catch (\Exception $e) {
                // si falla cualquier validación, se muestra el mensaje de error
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('app_perfil');
        }

        return $this->render('perfil/index.html.twig');
    }
}