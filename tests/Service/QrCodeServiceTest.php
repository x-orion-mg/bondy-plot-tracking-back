<?php

namespace App\Tests\Service;

use App\Service\QrCodeService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class QrCodeServiceTest extends TestCase
{
	public function testGenerateQrCode(): void
	{
		$qrCodeService = new QrCodeService();
		$url = 'https://example.com';

		$response = $qrCodeService->generateQrCode($url);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
		$this->assertStringStartsWith('data:image/png;base64,', $response->getContent());
		$this->assertEquals('image/png', $response->headers->get('Content-Type'));
	}
}
