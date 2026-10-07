<?php

namespace App\Twig;

use App\Service\FileService;
use App\Service\image\ImageCropper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
	private FileService $fileService;
	private ImageCropper $cropperService;

	public function __construct(FileService $fileService, ImageCropper $cropperService)
	{
		$this->cropperService = $cropperService;
		$this->fileService = $fileService;
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction('convert_file_to_base64', [$this->fileService, 'convertFileToBase64']),
			new TwigFunction('crop_to_base64', [$this->cropperService, 'cropToBase64']),
		];
	}
}
