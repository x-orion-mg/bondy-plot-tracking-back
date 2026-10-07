<?php

namespace App\Service\image;

use App\Exception\CustomException;

class ImageCropper {
	/**
	 * @throws CustomException
	 */
	public function cropToBase64( string $sourcePath, int $targetWidth = 480, int $targetHeight = 710 ): string {
		// Charger depuis URL si besoin
		if ( filter_var( $sourcePath, FILTER_VALIDATE_URL ) ) {
			$tempFile = tempnam( sys_get_temp_dir(), 'img_' );
			file_put_contents( $tempFile, file_get_contents( $sourcePath ) );
			$sourcePath = $tempFile;
		}

		// Chargement + mime type
		[ $sourceImage, $mimeType ] = $this->loadImageFromPath( $sourcePath );
		$sourceWidth  = imagesx( $sourceImage );
		$sourceHeight = imagesy( $sourceImage );

		// 🔁 Redimensionnement initial en 480x710
		$resizedImage = imagecreatetruecolor( $targetWidth, $targetHeight );
		if ( in_array( $mimeType, [ 'image/png', 'image/webp' ] ) ) {
			imagesavealpha( $resizedImage, true );
			$transparent = imagecolorallocatealpha( $resizedImage, 0, 0, 0, 127 );
			imagefill( $resizedImage, 0, 0, $transparent );
		}

		imagecopyresampled(
			$resizedImage,      // destination
			$sourceImage,       // source
			0, 0,               // dest x,y
			0, 0,               // source x,y
			$targetWidth, $targetHeight, // dest width/height
			$sourceWidth, $sourceHeight  // source width/height
		);

		imagedestroy( $sourceImage ); // libérer mémoire originale

		$sourceWidth  = $targetWidth;
		$sourceHeight = $targetHeight;

		// Image masquée (avec transparence si PNG/WebP)
		$maskedImage = imagecreatetruecolor( $sourceWidth, $sourceHeight );
		if ( in_array( $mimeType, [ 'image/png', 'image/webp' ] ) ) {
			imagesavealpha( $maskedImage, true );
			$transparent = imagecolorallocatealpha( $maskedImage, 0, 0, 0, 127 );
			imagefill( $maskedImage, 0, 0, $transparent );
		}

		// Polygon pour coupe diagonale douce
		$polygon = [
			0,
			10,
			$sourceWidth,
			0,
			$sourceWidth,
			$sourceHeight,
			0,
			$sourceHeight,
		];

		// Appliquer le masque
		for ( $y = 0; $y < $sourceHeight; $y ++ ) {
			for ( $x = 0; $x < $sourceWidth; $x ++ ) {
				if ( $this->pointInPolygon( $x, $y, $polygon ) ) {
					$color = imagecolorat( $resizedImage, $x, $y );
					imagesetpixel( $maskedImage, $x, $y, $color );
				}
			}
		}

		imagedestroy( $resizedImage );

		// 🔁 Base64 encode
		ob_start();
		match ( $mimeType ) {
			'image/jpeg' => imagejpeg( $maskedImage ),
			'image/png' => imagepng( $maskedImage ),
			'image/webp' => imagewebp( $maskedImage ),
			'image/gif' => imagegif( $maskedImage ),
			default => throw new CustomException( "Export non supporté pour $mimeType" ),
		};
		$imageData = ob_get_clean();

		imagedestroy( $maskedImage );

		return 'data:' . $mimeType . ';base64,' . base64_encode( $imageData );
	}

	private function pointInPolygon( int $x, int $y, array $polygon ): bool {
		$inside    = false;
		$numPoints = count( $polygon ) / 2;
		$j         = $numPoints - 1;

		for ( $i = 0; $i < $numPoints; $i ++ ) {
			$xi = $polygon[ $i * 2 ];
			$yi = $polygon[ $i * 2 + 1 ];
			$xj = $polygon[ $j * 2 ];
			$yj = $polygon[ $j * 2 + 1 ];

			$intersect = ( ( $yi > $y ) !== ( $yj > $y ) ) &&
			             ( $x < ( $xj - $xi ) * ( $y - $yi ) / ( ( $yj - $yi ) ?: 0.00001 ) + $xi );

			if ( $intersect ) {
				$inside = ! $inside;
			}

			$j = $i;
		}

		return $inside;
	}

	/**
	 * @throws CustomException
	 */
	private function loadImageFromPath( string $path ): array {
		$info = getimagesize( $path );
		if ( ! $info ) {
			throw new CustomException( "Type MIME introuvable : $path" );
		}

		$mime = $info['mime'];

		$image = match ( $mime ) {
			'image/jpeg' => imagecreatefromjpeg( $path ),
			'image/png' => imagecreatefrompng( $path ),
			'image/webp' => imagecreatefromwebp( $path ),
			'image/gif' => imagecreatefromgif( $path ),
			default => throw new CustomException( "Format non supporté : $mime" ),
		};

		return [ $image, $mime ];
	}
}
