<?php

namespace App\Service;

class CertificateService {
	private DownloadLinkGeneratorInterface $linkGenerator;

	public function __construct(DownloadLinkGeneratorInterface $linkGenerator) {
		$this->linkGenerator = $linkGenerator;
	}

	public function generateTemporaryLink(string $filename, int $expiresInMinutes = 10): string {
		return $this->linkGenerator->generateTemporaryLink($filename, $expiresInMinutes);
	}
}
