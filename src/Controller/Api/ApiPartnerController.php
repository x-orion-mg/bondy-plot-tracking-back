<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\PartnerRepository;
use App\Utils\GetResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/partner')]
class ApiPartnerController extends AbstractApiController
{
	private EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
	{
		parent::__construct($serializer);
		$this->entityManager    = $entityManager;
	}

	#[Route('/check-token', name: 'partner_check_token', methods: ['GET'])]
	public function checkToken(Request $request): JsonResponse
	{
		$token = $request->query->get('token');

		if (!$token) {
			return $this->createErrorResponse('Token is required', Response::HTTP_UNAUTHORIZED);
		}

		$partner = $this->entityManager->getRepository(User::class)->findOneBy(['token' => $token]);

		return $this->createSuccessResponse(['user' => $partner], ['user', 'uuid', 'token']);
	}
}
