<?php
namespace App\Service;

use App\Entity\Gallery;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileProcessingStrategyInterface {
	public function process(Gallery $gallery, UploadedFile $file): void;

	public function supports(string $mimeType): bool;
}
