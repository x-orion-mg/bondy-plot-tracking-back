<?php

namespace App\Message;

class ProcessImageDrone
{
    private string $filePath;
    private string $outputDir;
    private string $originalFileName;
    private int $parcelId;

    public function __construct(string $filePath, string $outputDir, string $originalFileName, int $parcelId)
    {
        $this->filePath = $filePath;
        $this->outputDir = $outputDir;
        $this->originalFileName = $originalFileName;
        $this->parcelId = $parcelId;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getOutputDir(): string
    {
        return $this->outputDir;
    }

    public function getOriginalFileName(): string
    {
        return $this->originalFileName;
    }

    public function getParcelId(): int
    {
        return $this->parcelId;
    }
}
