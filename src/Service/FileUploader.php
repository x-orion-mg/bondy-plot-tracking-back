<?php
// src/Service/FileUploader.php
namespace App\Service;

use App\Exception\CustomException;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
	private string $targetDirectory;
	private RequestStack $requestStack;
	private Packages $packages;
	public function __construct(
		RequestStack $requestStack,
		private SluggerInterface $slugger,
		Packages $packages
	) {
		$this->requestStack = $requestStack;
		$this->packages               = $packages;
	}

	/**
	 * @throws CustomException
	 */
	public function upload(UploadedFile $file): array {
		$request = $this->requestStack->getCurrentRequest();
		$baseUrl = $request->getSchemeAndHttpHost();
		$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$safeFilename = $this->slugger->slug($originalFilename);
		$fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
		$mimeType = $file->getMimeType();

		try {
			$original_name = $file->getClientOriginalName();
			$path_part     = pathinfo($original_name);
			$extension     = $path_part['extension'];
			$filename      = md5(uniqid());

			$filename_extension = $filename . '.' . $extension;
			$file->move($this->getTargetDirectory(), $filename_extension);
		} catch (FileException $e) {
			throw new CustomException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
		}

		return [
			'fileName' => $filename_extension, //$fileName,
			'mimeType' => $mimeType,
			'url' => $baseUrl .$this->packages->getUrl($this->getTargetDirectory() . '/' . $filename_extension),
		];
	}

	public function getTargetDirectory(): string
	{
		return $this->targetDirectory;
	}

	public function setTargetDirectory( string $string ): static {
		$this->targetDirectory = $string;
		return $this;
	}
}
