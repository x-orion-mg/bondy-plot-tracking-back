<?php

namespace App\Controller\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class ErrorController extends AbstractController
{
    public function show(\Throwable $exception): Response
    {
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return $this->redirectToRoute('login');
        }

        if ($statusCode === 404) {
            return $this->render('error/error404.html.twig', [
                'message' => $exception->getMessage(),
            ], new Response('', $statusCode));
        }

        if ($statusCode === 500) {
            return $this->render('error/error500.html.twig', [
                'message' => 'An internal server error occurred.',
            ], new Response('', $statusCode));
        }

        return $this->redirectToRoute('admin_dashboard');
    }
}

