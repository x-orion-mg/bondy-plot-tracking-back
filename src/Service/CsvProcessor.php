<?php

namespace App\Service;

class CsvProcessor implements CsvProcessorInterface {


	public function processCsvFile(string $filePath): array {
		$fileContent = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		return array_map(function($line) {
			return str_getcsv($line, ';');
		}, $fileContent);
	}

	public function validateHeaders(array $headers): bool {
		return  in_array('nom', $headers);
	}
}
