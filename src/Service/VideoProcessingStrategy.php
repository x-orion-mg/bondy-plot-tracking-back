<?php
// src/Service/VideoProcessingStrategy.php
namespace App\Service;

use App\Entity\Gallery;
use App\Exception\CustomException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use App\Utils\PathName;

class VideoProcessingStrategy implements FileProcessingStrategyInterface {
	private Filesystem $filesystem;
	private ServiceUploadFiles $filesService;
	private string $targetDirectory;
	private string $thumbnailDirectory;

	public function __construct(  Filesystem $filesystem, ServiceUploadFiles $filesService ) {
		$this->filesService       = $filesService;
		$this->filesystem         = $filesystem;
		$this->targetDirectory    = PathName::UPLOAD_GALLERIES;
		$this->thumbnailDirectory = PathName::UPLOAD_THUMBNAIL_GALLERIES;
	}

	public function supports( string $mimeType ): bool {
		return in_array( $mimeType, [ 'video/mp4', 'video/avi' ] );
	}

	/**
	 * @throws CustomException
	 */
	public function process( Gallery $gallery, UploadedFile $file ): void {
		// Ensure the target directory exists
		if ( ! $this->filesystem->exists( $this->targetDirectory ) ) {
			$this->filesystem->mkdir( $this->targetDirectory, 0777 );
		}

		// Ensure the thumbnail directory exists
		if ( ! $this->filesystem->exists( $this->thumbnailDirectory ) ) {
			$this->filesystem->mkdir( $this->thumbnailDirectory, 0777 );
		}
		// Use the setFileUrl method to get the file URL
		$fileUrl = $this->filesService->setFileUrl( $file, $this->targetDirectory );


		$thumbnailPath = $this->thumbnailDirectory .  pathinfo( $file->getClientOriginalName(), PATHINFO_FILENAME ) . '.jpg';

		try {
			$ffmpeg = FFMpeg::create([
				'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
				'ffprobe.binaries' => '/usr/bin/ffprobe',
				'timeout'          => 3600,
				'ffmpeg.threads'   => 12,
			]);

			$video = $ffmpeg->open( $fileUrl );
			$video->frame( TimeCode::fromSeconds( 1 ) )->save( $thumbnailPath );
		} catch ( \Exception $e ) {
			throw new CustomException( 'Failed to generate thumbnail: ' . $e->getMessage() );
		}
		preg_match('~^(https?://.+)(/uploads/galleries/.*)~', $fileUrl, $matches);
		$gallery->setUrl( $fileUrl );
		$gallery->setThumbnailUrl( $matches[1] . '/' . $thumbnailPath );
		$gallery->setType( 'video' );
	}

}
