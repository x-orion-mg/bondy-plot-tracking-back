<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ErrorController extends AbstractController
{
    public function show(\Throwable $exception): Response
    {
        // Déterminer le code d'état HTTP
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        if ($statusCode === 404) {
            return $this->render('admin/error/error404.html.twig', [
                'message' => $exception->getMessage(),
            ], new Response('', $statusCode));
        }

        // Gérer les autres erreurs (500, etc.)
        return $this->render('admin/error/error404.html.twig', [
            'message' => $exception->getMessage(),
        ], new Response('', $statusCode));
    }
}

