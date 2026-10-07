<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;

class FileDownloadService {
	private string $secretKey;

	public function __construct( ParameterBagInterface $params ) {
		$this->secretKey = $params->get( 'certificate_secret_key' );
	}

	public function handleFileDownload( Request $request, string $filename, string $filePath ): Response {
		$expirationTime = $request->query->get( 'expires' );
		$signature      = $request->query->get( 'signature' );

		if ( $expirationTime && $signature ) {
			return $this->validateSignatureAndDownload( $filename, $filePath, $expirationTime, $signature );
		}

		return new Response( "Access denied", 403 );
	}

	private function validateSignatureAndDownload( string $filename, string $filePath, string $expirationTime, string $signature ): Response {
		if ( time() <= (int) $expirationTime ) {
			$expectedSignature = hash_hmac( 'sha256', $filename . $expirationTime, $this->secretKey );
			if ( hash_equals( $expectedSignature, $signature ) ) {
				return new BinaryFileResponse( new File( $filePath ) );
			} else {
				return new Response( "Invalid signature", 403 );
			}
		} else {
			return new Response( "Link expired", 403 );
		}
	}
}
