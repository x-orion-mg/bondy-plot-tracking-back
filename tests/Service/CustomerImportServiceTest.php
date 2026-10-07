<?php

namespace App\Tests\Service;

use App\Entity\CustomerImport;
use App\Service\CustomerImportService;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class CustomerImportServiceTest extends TestCase
{
	private $entityManager;
	private $fileService;
	private $customerImportService;
	private $customerImportRepository;

	protected function setUp(): void
	{
		$this->entityManager = $this->createMock(EntityManagerInterface::class);
		$this->fileService = $this->createMock(FileService::class);
		$this->customerImportRepository = $this->createMock(EntityRepository::class);

		$this->entityManager
			->method('getRepository')
			->with(CustomerImport::class)
			->willReturn($this->customerImportRepository);

		$this->customerImportService = new CustomerImportService($this->entityManager, $this->fileService);
	}

	public function testIsFileAlreadyProcessedReturnsTrueIfFileExists()
	{
		$this->customerImportRepository
			->method('findOneBy')
			->willReturn(new CustomerImport());

		$result = $this->customerImportService->isFileAlreadyProcessed('import-user-valid.csv');
		$this->assertTrue($result);
	}

	public function testIsFileAlreadyProcessedReturnsFalseIfFileDoesNotExist()
	{
		$this->customerImportRepository
			->method('findOneBy')
			->willReturn(null);

		$result = $this->customerImportService->isFileAlreadyProcessed('file-not-exist.csv');
		$this->assertFalse($result);
	}
}
