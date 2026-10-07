<?php

namespace App\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReportHistoryDownloadLinkGenerator implements DownloadLinkGeneratorInterface {
	private UrlGeneratorInterface $urlGenerator;
	private string $secretKey;

	public function __construct(UrlGeneratorInterface $urlGenerator, string $secretKey) {
		$this->urlGenerator = $urlGenerator;
		$this->secretKey = $secretKey;
	}

	public function generateTemporaryLink(string $filename, int $expiresInMinutes = 10): string {
		$expirationTime = time() + ($expiresInMinutes * 60);
		$hash = hash_hmac('sha256', $filename . $expirationTime, $this->secretKey);

		return $this->urlGenerator->generate('download_report_history', [
			'filename' => $filename,
			'expires' => $expirationTime,
			'signature' => $hash,
		], UrlGeneratorInterface::ABSOLUTE_URL);
	}
}
