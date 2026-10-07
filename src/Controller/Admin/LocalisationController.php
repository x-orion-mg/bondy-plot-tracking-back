<?php

namespace App\Controller\Admin;

use App\Repository\LocalisationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/localisation')]
final class LocalisationController extends AbstractController
{

    public function __construct(
        private LocalisationRepository $manage_localisation,
        private EntityManagerInterface $entity_manager)
    {
        $this->manage_localisation = $manage_localisation;
        $this->entity_manager = $entity_manager;
    }

    #[Route(name: 'localisation_list', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/localisation/index.html.twig', [
            'localisations' => $this->manage_localisation->findAll(),
        ]);
    }

    
    #[Route('/edit', name: 'localisation_edit', methods: ['GET','POST'])]
    public function edit(Request $request): Response
    {
        $response = false;
        $data     = $request->request->all();
        $shape_id = array_key_exists('shape_id', $data) && !empty($data['shape_id']) ? $data['shape_id'] : '';
        $localisation = $this->manage_localisation->findOneBy(['shapeId' => $shape_id]);

        if ( $localisation ) {
            $localisation->setRegion($data['region']);
            $localisation->setLatitude($data['latitude']);
            $localisation->setLongitude($data['longitude']);

            $this->entity_manager->flush();
            $response = true;
        }

        return new JsonResponse(['success' => $response]);
    }
}
