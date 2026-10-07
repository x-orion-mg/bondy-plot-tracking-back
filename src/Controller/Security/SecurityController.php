<?php

namespace App\Controller\Security;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Security;
use App\Security\PasswordResetService;
use App\Service\MailService;

class SecurityController extends AbstractController
{
    private $security;
    private $mailService;
    public function __construct(Security $security, MailService $mailService)
    {
        $this->security = $security;
        $this->mailService = $mailService;
    }

    #[Route(path: '/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() || $this->security->getUser()) {
            return $this->redirectToRoute('admin_dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/', name: 'home')]
    public function index()
    {
        if ($this->security->getUser()) {
            return $this->redirectToRoute('admin_dashboard');
        }
        return $this->redirectToRoute('login');
    }

    #[Route('/forgot-password', name: 'forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request): Response
    {
        return $this->render('security/forgot-password.html.twig');
    }

    #[Route('/reset-link', name: 'send_reset_link', methods: ['POST'])]
    public function sendResetLink(Request $request, UserRepository $userRepository): Response
    {
        $params = $request->request->all();
        $email = array_key_exists('email', $params) && !empty($params['email']) ? trim($params['email']) : '';
        $user = $userRepository->findOneBy(['email' => $email]);

        if (empty($email)) {
            $this->addFlash('error', 'Veuillez entrer une adresse e-mail.');
            return $this->redirectToRoute('forgot_password');
        }
        if (!$user) {
            $this->addFlash('error', 'Aucun utilisateur trouvé avec cette adresse e-mail.');
            return $this->redirectToRoute('forgot_password');
        }
        $this->mailService->sendResetLink($email);
        $this->addFlash('success', 'Un lien de réinitialisation a été envoyé à votre adresse e-mail.');

        return $this->redirectToRoute('login');
    }

    #[Route('/reset-password/{uuid}', name: 'reset_password_get', methods: ['GET'])]
    public function resetPasswordGet(string $uuid, UserRepository $userRepository): Response
    {
        if (empty($uuid)) {
            $this->addFlash('error', 'Adresse e-mail invalide.');
            return $this->redirectToRoute('forgot_password');
        }
        $user = $userRepository->findOneBy(['uuid' => $uuid]);
        if (!$user) {
            $this->addFlash('error', 'Aucun utilisateur trouvé avec cette adresse e-mail.');
            return $this->redirectToRoute('forgot_password');
        }
        return $this->render('security/reset-password.html.twig', [
            'uuid' => $uuid,
        ]);
    }

    #[Route('/update-password/{uuid}', name: 'reset_password_post', methods: ['POST'])]
    public function resetPassword(Request $request, string $uuid, PasswordResetService $passwordResetService): Response
    {
        $password = $request->request->get('user_password');
        $confirmPassword = $request->request->get('user_confirm_password');

        // Vérification des mots de passe
        if ($password !== $confirmPassword) {
            $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            return $this->redirectToRoute('reset_password_get', ['uuid' => $uuid]);
        }

        // Appel au service pour réinitialiser le mot de passe
        $success = $passwordResetService->resetPassword($uuid, $password);

        if ($success) {
            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès. Veuillez vous reconnecter avec vos nouvelles informations.');
            return $this->redirectToRoute('login');
        }

        $this->addFlash('error', 'Une erreur est survenue lors de la réinitialisation du mot de passe.');
        return $this->redirectToRoute('reset_password_get', ['uuid' => $uuid]);
    }
}
