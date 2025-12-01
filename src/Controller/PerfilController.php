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
    #[Route('/mi-perfil', name: 'app_perfil')]
    public function index(Request $request, UsuarioManager $usuarioManager): Response
    {
        /** @var Usuario $user */
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            
            // Recolecto todos los datos del formulario
            $datos = $request->request->all();

            try {
                // Le paso al Manager:
                // 1. EL USUARIO que quiero editar ($user)
                // 2. LOS DATOS nuevos ($datos)
                $usuarioManager->actualizar($user, $datos);

                $this->addFlash('success', '¡Tus datos se actualizaron correctamente!');

            } catch (\Exception $e) {
                // Si falla cualquier validación (regex o password), muestro el error
                $this->addFlash('error', $e->getMessage());
            }

            // Siempre redirijo al mismo lugar para refrescar
            return $this->redirectToRoute('app_perfil');
        }

        return $this->render('perfil/index.html.twig');
    }
}