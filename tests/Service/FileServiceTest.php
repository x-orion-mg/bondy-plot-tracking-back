<?php

namespace App\Tests\Service;

use App\Service\FileService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileServiceTest extends TestCase
{
	private $fileService;

	protected function setUp(): void
	{
		$this->fileService = new FileService();
	}

	public function testIsInvalidFileReturnsTrueForNullFile()
	{
		$this->assertTrue($this->fileService->isInvalidFile(null));
	}

	public function testIsInvalidFileReturnsFalseForValidCsvFile()
	{
		$file = $this->createMock(UploadedFile::class);
		$file->method('getClientOriginalExtension')->willReturn('csv');
		$file->method('getMimeType')->willReturn('text/plain');

		$this->assertFalse($this->fileService->isInvalidFile($file));
	}

	public function testIsInvalidFileReturnsTrueForInvalidFile()
	{
		$file = $this->createMock(UploadedFile::class);
		$file->method('getClientOriginalExtension')->willReturn('txt');
		$file->method('getMimeType')->willReturn('text/plain');

		$this->assertTrue($this->fileService->isInvalidFile($file));
	}

	public function testIsInvalidFileContentReturnsTrueForEmptyContent()
	{
		$this->assertTrue($this->fileService->isInvalidFileContent([]));
	}

	public function testIsInvalidFileContentReturnsFalseForValidContent()
	{
		$this->assertFalse($this->fileService->isInvalidFileContent([['data1'], ['data2']]));
	}
}
