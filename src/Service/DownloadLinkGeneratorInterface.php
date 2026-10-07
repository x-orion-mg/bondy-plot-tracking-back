<?php

namespace App\Service;

interface DownloadLinkGeneratorInterface {
	public function generateTemporaryLink(string $filename, int $expiresInMinutes = 10): string;
}
