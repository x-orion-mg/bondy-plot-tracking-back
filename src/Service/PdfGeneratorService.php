<?php

// src/Service/PdfGenerator.php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class PdfGeneratorService
{
	/**
	 * Generates a PDF from the provided HTML content.
	 *
	 * @param string $htmlContent The HTML content to be converted into a PDF.
	 * @param string $orientation The orientation of the PDF ('portrait' or 'landscape'). Default is 'landscape'.
	 *
	 * @return Response The HTTP response containing the generated PDF.
	 */
	public function generatePdf(string $htmlContent, string $orientation = 'landscape'): Response
	{
		// Configuration de Dompdf
		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isPhpEnabled', true);
		$options->set('defaultFont', 'DejaVu Sans');
		$dompdf = new Dompdf($options);

		$dompdf->loadHtml($htmlContent);
		$dompdf->setPaper('A4', $orientation);
		$dompdf->render();

		$pdfOutput = $dompdf->output();

		return new Response(
			$pdfOutput,
			Response::HTTP_OK,
			[
				'Content-Type' => 'application/pdf',
				'Content-Disposition' => 'attachment; filename="document.pdf"',
			]
		);
	}
}

