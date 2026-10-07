<?php

namespace App\Controller\Admin;

use App\Entity\Partner;
use App\Entity\Profile;
use App\Entity\User;
use App\Utils\Utils;
use App\Utils\PathName;
use App\Exception\CustomException;
use App\Repository\PartnerRepository;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use App\Service\ServiceUploadFiles;
use App\Service\FileUploader;
use App\Service\PartnerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/admin/partner')]
final class PartnerController extends AbstractController
{
    private PartnerService $partnerService;
    private string $frontendBaseUrl;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Utils                  $utils,
        private ServiceUploadFiles     $serviceUploadFiles,
        PartnerService                 $partnerService,
        ParameterBagInterface          $params
    )
    {
        $this->entityManager = $entityManager;
        $this->utils = $utils;
        $this->serviceUploadFiles = $serviceUploadFiles;
        $this->partnerService = $partnerService;
        $this->frontendBaseUrl = $params->get('frontend_base_url');
    }

    #[Route(name: 'partner_index', methods: ['GET'])]
    public function index(ProfileRepository $profileRepository, UserRepository $userRepository): Response
    {
        $profile = $profileRepository->getProfileByCode('PARTNER');
        $partners = $userRepository->findAllUserPartner($profile->getId());

        return $this->render('admin/partner/index.html.twig', [
            'partners' => $partners
        ]);
    }

    /**
     * @throws CustomException
     */
    #[Route('/new', name: 'partner_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FileUploader $fileUploader, PartnerRepository $partnerRepository): Response
    {
        $response = false;
        $data = $request->request->all();
        $files = $request->files->all();

        $partnerId = array_key_exists('partner_id', $data) && !empty($data['partner_id']) ? intval($data['partner_id']) : 0;
        $name = array_key_exists('partner_name', $data) && !empty($data['partner_name']) ? strtoupper($data['partner_name']) : '';
        $email = array_key_exists('partner_email', $data) && !empty($data['partner_email']) ? strtolower($data['partner_email']) : '';
        $phone = array_key_exists('partner_phone', $data) && !empty($data['partner_phone']) ? $data['partner_phone'] : '';
        $address = array_key_exists('partner_address', $data) && !empty($data['partner_address']) ? $data['partner_address'] : '';

        if (!$name && !$email) {
            $message = 'Veuillez remplir tous les champs obligatoires.';
        } else {
            if ($partnerId > 0) {
                $userPartner = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $partnerId]);
                if (!$userPartner) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Partenaire non trouvé'
                    ]);
                }
                $message = 'Modification effectuée avec succès';
            } else {
                $checkEmail = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($checkEmail) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Adresse EMAIL est déjà utilisée.'
                    ]);
                }
                $userPartner = new User();
                $userPartner->setEmail($email);
                $profilePartner = $this->entityManager->getRepository(Profile::class)->findOneBy(['code' => 'PARTNER']);
                $userPartner->setProfile($profilePartner);
                $userPartner->setRoles(['ROLE_PARTNER']);
                $userPartner->setEnabled(true);
                $userPartner->setPassword($this->utils->generateToken());

                $message = 'Création effectuée avec succès';
            }

            $userPartner->setLastName($name);
            $userPartner->setPhone($phone);
            $userPartner->setAddress($address);

            if (!empty($files['uploadFile']) && $files['uploadFile']->isValid()) {
                $fileUrl = $this->serviceUploadFiles->uploadBannerImage(PathName::UPLOAD_LOGO_PARTNER, $files);
                $userPartner->setLogo($fileUrl);
            }

            $this->entityManager->persist($userPartner);
            $this->entityManager->flush();

            $response = true;
        }

        return $this->json([
            'success' => $response,
            'message' => $message
        ]);
    }

    #[Route('/delete', name: 'partner_delete', methods: ['POST'])]
    public function delete(Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $response = false;
        $message = 'Erreur lors de la suppression';
        $data = $request->request->all();
        $partnerId = array_key_exists('id', $data) && !empty($data['id']) ? intval($data['id']) : 0;

        // Validate CSRF token
        $csrfToken = $data['_token'] ?? '';
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete_partner', $csrfToken))) {
            return $this->json([
                'status' => false,
                'message' => 'Token CSRF invalide'
            ]);
        }

        if ($partnerId > 0) {
            $partner = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $partnerId]);
            if ($partner) {
                $hasProject = $this->entityManager->getRepository(User::class)->findPartnerProject($partner->getProfile()->getId(), $partnerId, 'DESC');
                if ($hasProject > 0) {
                    $message = "Impossible de supprimer le partenaire, il a encore des projets associés.";
                    $response = false;
                } else {
                    $this->entityManager->remove($partner);
                    $this->entityManager->flush();

                    $this->partnerService->deletePartner($partner);
                    $message = 'Partenaire supprimé avec succès.';
                    $response = true;
                }
            }
        }

        return $this->json([
            'status' => $response,
            'message' => $message
        ]);
    }

    #[Route('/find-partner-link', name: 'find_partner_link', methods: ['GET'])]
    public function findPartnerLink(Request $request): JsonResponse
    {
        $partnerId = $request->query->getInt('id', 0);
        $link = '';
        $response = false;

        if ($partnerId > 0) {
            $partner = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $partnerId]);
            if ($partner) {
                if (!$partner->getToken()) {
                    $newToken = $this->utils->generateToken();
                    $partner->setToken($newToken);
                    $this->entityManager->persist($partner);
                    $this->entityManager->flush();
                }
                $link = rtrim($this->frontendBaseUrl, '/') . '/?token=' . $partner->getToken();
                $response = true;
            }
        }

        return new JsonResponse([
            'status' => $response,
            'data' => $link
        ]);
    }
}
