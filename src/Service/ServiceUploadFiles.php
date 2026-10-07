<?php

namespace App\Service;

use App\Exception\CustomException;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;

class ServiceUploadFiles
{
    public function __construct(
        private FileUploader $fileUploader,
        private RequestStack $requestStack,
        private Packages $packages
    ) {
        $this->fileUploader = $fileUploader;
        $this->requestStack = $requestStack;
        $this->packages     = $packages;
    }

	/**
	 * @throws CustomException
	 */
	public function uploadBannerImage( string $pathname, array $files = [] ): string
    {
        $fileUrl = '';
        $file    = array_key_exists('uploadFile', $files) ? $files['uploadFile'] : NULL;
        if ( !empty($file) ) {
            $fileUrl = $this->setFileUrl($file, $pathname);
        }

        return $fileUrl;
    }

	/**
	 * @throws CustomException
	 */
	public function uploadImageDrone( string $pathname, array $files = [] ): string
    {
        $fileUrl = '';
        $file = !empty($files['droneImage']) ? $files['droneImage'] : [];
        if ( !empty($file) ) {
            $fileUrl = $this->setFileUrl($file, $pathname);
        }

        return $fileUrl;
    }

	/**
	 * @throws CustomException
	 */
	public function uploadIcon( string $pathname, array $files = [] ): string
    {
        $fileUrl = '';
        $file = !empty($files['iconFile']) ? $files['iconFile'] : [];
        if ( !empty($file) ) {
            $fileUrl = $this->setFileUrl($file, $pathname);
        }

        return $fileUrl;
    }

	/**
	 * @throws CustomException
	 */
	public function setFileUrl( $file, $pathname ): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = $request->getSchemeAndHttpHost();
        $this->fileUploader->setTargetDirectory($pathname);
        $image_name = $this->fileUploader->upload($file);

        return $baseUrl . $this->packages->getUrl($pathname . $image_name['fileName']);
    }
}
