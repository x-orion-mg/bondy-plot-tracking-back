<?php

namespace App\Service;

interface CsvProcessorInterface {
	public function processCsvFile(string $filePath): array;
	public function validateHeaders(array $headers): bool;
}
