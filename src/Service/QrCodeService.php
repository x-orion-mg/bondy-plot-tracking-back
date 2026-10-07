<?php

namespace App\Service;

use App\Utils\PathName;
use App\Utils\Utils;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Asset\Packages;

class QrCodeService
{
    private string $frontendBaseUrl;
    private Utils $utils;
    private Packages $assetsManager;

    public function __construct(ParameterBagInterface $params, Utils $utils, Packages $assetsManager)
    {
        $this->frontendBaseUrl = $params->get('frontend_base_url');
        $this->utils = $utils;
        $this->assetsManager = $assetsManager;
    }

    public function generateFrontendUrl(string $path): string
    {
        return rtrim($this->frontendBaseUrl, '/') . '/' . ltrim($path, '/');
    }

    public function generateQrCode(string $url, string $format = 'base64'): mixed
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->validateResult(false)
            ->build();

        if ($format === 'png') {
            $trackCode = $this->utils->getTrackCodeUrl($url);
            $directory = PathName::EXPORT_QRCODE_CUSTOMERS_FINALS;
            $fileName = 'qrcode_' . $trackCode . '.png';
            $filePath = $directory . $fileName;

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            file_put_contents($filePath, $result->getString());

            return [
                'file_path' => '/' . $filePath,
                'file_name' => $fileName,
            ];
        }

        return new Response('data:image/png;base64,' . base64_encode($result->getString()), Response::HTTP_OK, ['Content-Type' => 'image/png']);
    }
}
