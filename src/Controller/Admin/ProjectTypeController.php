<?php

namespace App\Controller\Admin;

use App\Repository\ProjectTypeRepository;
use App\Repository\ProjectParentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/projects/types')]
class ProjectTypeController extends AbstractController
{
    public function __construct(
        private ProjectTypeRepository   $manage_project_type,
        private ProjectParentRepository $manage_project_parent
    )
    {
        $this->manage_project_type = $manage_project_type;
        $this->manage_project_parent = $manage_project_parent;
    }

    #[Route('/', name: 'project_type_index')]
    public function index(): Response
    {
        $data = $this->manage_project_type->findBy( [], ['id' => 'DESC'] );
        return $this->render('admin/project-type/index.html.twig', [
            'project_types' => $data
        ]);
    }

    #[Route('/create', name: 'project_type_create', methods: ['POST'])]
    public function create( Request $request ): Response
    {
        $data = $request->request->all();

        $typeId = intval($data['id'] ?? 0);
        $name   = $data['label'] ?? '';
        $color  = $data['color'] ?? '';

        if (empty($name) && empty($color)) {
            return $this->json(['success' => false]);
        }
        $existingType = $this->manage_project_type->findOneBy(['id' => $typeId]);

        if ($existingType) {
            $this->manage_project_type->updateType($typeId, $name, $color);
        } else {
            $typeByName = $this->manage_project_type->findByLabel($name);
            if ($typeByName) {
                return $this->json(['success' => false]);
            }
            $this->manage_project_type->createType($name, $color);
        }

        return $this->json(['success' => true]);
    }

    #[Route('/delete', name: 'project_type_delete', methods: ['DELETE'])]
    public function delete(Request $request): Response
    {
        $response = false;
        $message = 'Erreur lors de la suppression';
        $data    = $request->request->all();
        $type_id = isset($data['id']) ? intval($data['id']) : 0;
        $exist_prj_parent = $this->manage_project_parent->fetchIfExistsInParentProject($type_id);

        if ( $type_id > 0 && !$exist_prj_parent) {
            $this->manage_project_type->deleteType($type_id);
            $response = true;
            $message = 'Suppression effectuée avec succès';
        }
        return $this->json([
            'success' => $response,
            'message' => $message
        ]);
    }
}
