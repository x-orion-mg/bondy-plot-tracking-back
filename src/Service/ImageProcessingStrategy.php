<?php

namespace App\Service;

use App\Entity\Gallery;
use App\Exception\CustomException;
use Imagine\Image\ManipulatorInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Imagine\Imagick\Imagine;
use Imagine\Image\Box;
use App\Utils\PathName;

class ImageProcessingStrategy implements FileProcessingStrategyInterface {
	private Filesystem $filesystem;
	private ServiceUploadFiles $filesService;
	private string $targetDirectory;
	private string $thumbnailDirectory;
	private string $projectDir;

	public function __construct( Filesystem $filesystem, ServiceUploadFiles $filesService, ParameterBagInterface $params ) {
		$this->filesService       = $filesService;
		$this->filesystem         = $filesystem;
		$this->targetDirectory    = PathName::UPLOAD_GALLERIES ;
		$this->thumbnailDirectory = PathName::UPLOAD_THUMBNAIL_GALLERIES ;
		$this->projectDir         = $params->get('kernel.project_dir');
	}

	public function supports( string $mimeType ): bool {
		return in_array( $mimeType, [ 'image/jpeg', 'image/png' ] );
	}

	/**
	 * @throws \Exception
	 */
	#[NoReturn] public function process( Gallery $gallery, UploadedFile $file ): void {
		// Use the setFileUrl method to get the file URL
		$fileUrl = $this->filesService->setFileUrl( $file, $this->targetDirectory );

		$thumbnailPath = $this->thumbnailDirectory . $file->getClientOriginalName();

		// Ensure the thumbnail directory exists
		if ( ! $this->filesystem->exists( $this->thumbnailDirectory ) ) {
			$this->filesystem->mkdir( $this->thumbnailDirectory, 0777 );
		}
		preg_match('~^(https?://.+)(/uploads/galleries/.*)~', $fileUrl, $matches);

		$imageDir = $this->projectDir .'/public'. $matches[2];
		if (!file_exists($imageDir)) {
			throw new CustomException("Image introuvable : $imageDir");
		}

		if (!is_readable($imageDir)) {
			throw new CustomException("Image illisible (droits ?) : $imageDir");
		}
		$imagesDir = $this->projectDir .'/public'. $matches[2];
		$imagine = new Imagine();
		$imagine->open( $imagesDir )
		        ->thumbnail( new Box( 200, 200 ), ManipulatorInterface::THUMBNAIL_OUTBOUND )
		        ->save( $thumbnailPath );

		$gallery->setUrl( $fileUrl );
		$gallery->setThumbnailUrl( $matches[1].'/'.$thumbnailPath );

		// Check if the image is a 360 image
		if ($this->is360Image($fileUrl)) {
			$gallery->setType('image_360');
		} else {
			$gallery->setType('image');
		}
	}
	private function is360Image($fileUrl): bool {
		$is360 = false;

		// Vérifier les données EXIF
		$exifData = @exif_read_data($fileUrl);
		if ($exifData) {
			// Recherche dans EXIF pour 'Software' ou 'Model' contenant '360'
			if ((isset($exifData['Software']) && str_contains($exifData['Software'], '360')) ||
			    (isset($exifData['Model']) && str_contains($exifData['Model'], '360'))) {
				$is360 = true;
			}

			// Vérification de ProjectionType pour une image 360°
			if (!$is360 && isset($exifData['ProjectionType']) && $exifData['ProjectionType'] === 'equirectangular') {
				$is360 = true;
			}
		}

		// Vérification des métadonnées XMP via exiftool (si vous avez accès à cette bibliothèque)
		if (!$is360) {
			$xmp = @get_meta_tags($fileUrl);
			if (isset($xmp['xmp']) && strpos($xmp['xmp'], '360') !== false) {
				$is360 = true;
			}
		}

		// Vérification du ratio de l'image
		if (!$is360 && $this->check360Dimensions($fileUrl)) {
			$is360 = true;
		}

		return $is360;
	}

	private function check360Dimensions($fileUrl) {
		list($width, $height) = getimagesize($fileUrl);
		return ($width / $height >= 2); // Un ratio de 2:1 ou plus comme indicateur
	}

}
