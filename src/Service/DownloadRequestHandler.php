<?php

namespace App\Service;

use App\Entity\Certificate;
use App\Entity\ReportHistory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class DownloadRequestHandler {
	private EntityManagerInterface $entityManager;
	private UrlGeneratorInterface $urlGenerator;
	private string $secretKey;

	public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, ParameterBagInterface $params) {
		$this->entityManager = $entityManager;
		$this->urlGenerator = $urlGenerator;
		$this->secretKey    = $params->get( 'certificate_secret_key' );
	}

	public function handleDownloadRequest(string $uuid, int $code, string $type): string {
		// Validate parameters
		if (!$this->validateUser($uuid)) {
			throw new \InvalidArgumentException("Invalid user");
		}

		if (!$this->validateCode($uuid, $code)) {
			throw new \InvalidArgumentException("Invalid code");
		}

		if (!$this->validateRequestType($type)) {
			throw new \InvalidArgumentException("Invalid request type");
		}

		return $this->processRequest($uuid, $type);
	}

	private function validateUser(string $uuid): bool {
		$user = $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $uuid]);
		return $user !== null;
	}

	private function validateCode(string $uuid, int $code): bool {
		if (strlen((string)$code) !== 4) {
			return false;
		}

		$user = $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $uuid]);
		if ($user === null) {
			return false;
		}

		return $user->getOptionValue('access_code') === $code;
	}

	private function validateRequestType(string $type): bool {
		return in_array($type, ['report-history', 'certificate']);
	}

	private function processRequest(string $uuid, string $type): string {
		$user = $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $uuid]);

		if ($type === 'certificate') {
			$certificate = $this->entityManager->getRepository(Certificate::class)->findOneBy(
				['user' => $user],
				['createdAt' => 'DESC']
			);
			if ($certificate === null) {
				throw new \InvalidArgumentException("Certificate not found for the user.");
			}
			$filName = $certificate->getFileName();
			$linkGenerator = new CertificateDownloadLinkGenerator($this->urlGenerator, $this->secretKey);
		} elseif ($type === 'report-history') {
			$reportHistory = $this->entityManager->getRepository(ReportHistory::class)->findOneBy(
				['user' => $user],
				['createdAt' => 'DESC']
			);
			if ($reportHistory === null) {
				throw new \InvalidArgumentException("Report history not found for the user.");
			}
			$filName = $reportHistory->getFileName();
			$linkGenerator = new ReportHistoryDownloadLinkGenerator($this->urlGenerator, $this->secretKey);
		} else {
			throw new \InvalidArgumentException("Invalid request type.");
		}

		$certificateService = new CertificateService($linkGenerator);
		return $certificateService->generateTemporaryLink($filName);
	}
}
