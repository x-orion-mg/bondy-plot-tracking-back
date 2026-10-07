<?php

namespace App\Service;

use App\Exception\CustomException;
use App\Utils\PathName;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;

class FileService {

	private Filesystem $filesystem;

	public function __construct( Filesystem $filesystem ) {
		$this->filesystem = $filesystem;
	}
	public function isInvalidFile( ?UploadedFile $file ): bool {
		return ! $file || $file->getClientOriginalExtension() !== 'csv' || $file->getMimeType() !== 'text/plain';
	}

	public function moveFile( UploadedFile $file ): string {
		$filePath = PathName::UPLOAD_CSV_CUSTOMER_IMPORT . '/' . $file->getClientOriginalName();
		$file->move( PathName::UPLOAD_CSV_CUSTOMER_IMPORT, $file->getClientOriginalName() );

		return $filePath;
	}

	public function isInvalidFileContent( array $fileContent ): bool {
		return ! $fileContent || count( $fileContent ) < 2;
	}

	public function ensureDirectoryExists(string $directory): void
	{
		if (!$this->filesystem->exists($directory)) {
			$this->filesystem->mkdir($directory, 0777); // Permet de créer le répertoire avec les bonnes permissions
		}
	}

	/**
	 * @throws CustomException
	 */
	public function convertFileToBase64(string $filePathOrUrl): string {
		// Check if the input is a URL
		if (filter_var($filePathOrUrl, FILTER_VALIDATE_URL)) {
			$data = @file_get_contents($filePathOrUrl);
			if ($data === false) {
				throw new CustomException("Failed to fetch data from URL: {$filePathOrUrl}");
			}

			$mimeType = $this->getMimeTypeFromUrl($filePathOrUrl, $data);
		} else {
			// Handle local file paths
			if (!file_exists($filePathOrUrl)) {
				throw new CustomException("File not found: {$filePathOrUrl}");
			}

			if (filesize($filePathOrUrl) === 0) {
				throw new CustomException("File is empty: {$filePathOrUrl}");
			}

			$mimeType = mime_content_type($filePathOrUrl);
			if ($mimeType === false) {
				throw new CustomException("Unable to determine MIME type for file: {$filePathOrUrl}");
			}

			$data = file_get_contents($filePathOrUrl);
			if ($data === false) {
				throw new CustomException("Failed to read file: {$filePathOrUrl}");
			}
		}

		return 'data:' . $mimeType . ';base64,' . base64_encode($data);
	}

	/**
	 * Determine MIME type from URL or fallback to default.
	 */
	private function getMimeTypeFromUrl(string $url, string $data): string {
		$pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
		$extension = $pathInfo['extension'] ?? '';

		// Map common extensions to MIME types
		$mimeTypes = [
			'png' => 'image/png',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif' => 'image/gif',
			'webp' => 'image/webp',
			'svg' => 'image/svg+xml',
		];

		return $mimeTypes[$extension] ?? 'application/octet-stream';
	}


}
