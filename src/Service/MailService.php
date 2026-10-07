<?php

namespace App\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Repository\UserRepository;


class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private UserRepository $userRepository;
    private RequestStack $requestStack;
    private UrlGeneratorInterface $router;

    public function __construct(
        MailerInterface       $mailer,
        Environment           $twig,
        UserRepository        $userRepository,
        RequestStack          $requestStack,
        UrlGeneratorInterface $router
    )
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
        $this->router = $router;
    }

    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function sendEmail(string $to, string $subject, string $template, array $context = [], ?object $triggeringEntity = null): void
    {
        $htmlContent = $this->twig->render($template, $context);

        $email = (new Email())
            ->from('no-reply@bondy.com')
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);
        if ($triggeringEntity) {
            $email->getHeaders()->addTextHeader('X-Triggering-Entity', get_class($triggeringEntity) . ':' . $triggeringEntity->getId());
        }
        $this->mailer->send($email);
    }

    public function sendResetLink(string $email): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return false; // L'e-mail n'existe pas
        }

        // Récupérer le domaine dynamiquement
        $resetLink = $this->router->generate(
            'reset_password_get',
            ['uuid' => $user->getUuid()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $emailMessage = (new Email())
            ->from('no-reply@bondy.com')
            ->to($email)
            ->subject('Réinitialisation de votre mot de passe')
            ->html(sprintf('<p>Bonjour,</p><p>Veuillez cliquer sur le lien suivant pour réinitialiser votre mot de passe :</p><a href="%s">%s</a>', $resetLink, $resetLink));

        $this->mailer->send($emailMessage);

        return true;
    }
}
