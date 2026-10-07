<?php

namespace App\Controller\Api;

use App\Repository\ProjectTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Utils\GetResponse;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/v1/projects/types')]
class ApiProjectTypeController extends AbstractController
{
    private $projectTypeService;
    private $responseManager;

    public function __construct( ProjectTypeRepository $projectTypeService, GetResponse $responseManager ) {
		$this->projectTypeService  = $projectTypeService;
		$this->responseManager = $responseManager;
	}

    #[Route('/', name: 'api_project_type_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->responseManager->response([
            'status' => 'success',
            'data'   => $this->projectTypeService->fetchAllTypes()
        ], JsonResponse::HTTP_OK);
    }
}
