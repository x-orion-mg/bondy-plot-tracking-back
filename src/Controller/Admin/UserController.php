<?php

namespace App\Controller\Admin;

use App\Utils\PathName;
use App\Utils\Utils;
use App\Entity\Certificate;
use App\Entity\ParcelTreeSpecies;
use App\Entity\Profile;
use App\Entity\ReportHistory;
use App\Entity\User;
use App\Entity\UserOption;
use App\Repository\ParcelRepository;
use App\Repository\ProfileRepository;
use App\Repository\ProjectChildRepository;
use App\Repository\PartnerCustomerRepository;
use App\Service\ExportExcel;
use App\Service\PartnerService;
use App\Service\QrCodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function PHPUnit\Framework\isNull;

#[Route('/admin/users')]
class UserController extends AbstractController
{
    const DATE_FORMAT = 'd/m/Y';
    private EntityManagerInterface $entityManager;
    private ProjectChildRepository $projectChildRepository;
    private ParcelRepository $parcelRepository;
    private ExportExcel $exportExcel;
    private $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProjectChildRepository $projectChildRepository,
        ParcelRepository       $parcelRepository,
        ExportExcel            $exportExcel,
        UserPasswordHasherInterface $passwordHasher,
    )
    {
        $this->entityManager = $entityManager;
        $this->projectChildRepository = $projectChildRepository;
        $this->parcelRepository = $parcelRepository;
        $this->exportExcel = $exportExcel;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/list', name: 'user_list', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $params = $request->query->all();
        $isEnabled = array_key_exists('isEnabled', $params) && !empty($params['isEnabled']) ? intval($params['isEnabled']) : null;
        $users = $this->entityManager->getRepository(User::class)->findUsersByAdminProfiles($isEnabled);
        $profiles = $this->entityManager->getRepository(Profile::class)->findAll();

        return $this->render('admin/user/user-list.html.twig', [
            'users' => $users,
            'profiles' => $profiles,
        ]);
    }

    #[Route('/{id}/delete', name: 'user_delete', methods: ['DELETE'])]
    public function delete(int $id, Utils $utils): Response
    {
        $currentUser = $this->getUser();
        if (!$utils->isAdmin($currentUser)) {
            return $this->json([
                'success' => false,
                'message' => 'Accès refusé. Vous n\'avez pas les droits nécessaires pour effectuer cette action.'
            ], Response::HTTP_FORBIDDEN);
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès.'
        ], Response::HTTP_OK);
    }

    #[Route('/{id}/customer-finals', name: 'user_customer_finals', methods: ['GET'])]
    public function manageQrCode(int $id, PartnerCustomerRepository $partnerCustomerRepository, ProfileRepository $profile): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        $profile = $profile->getProfileByCode('Client');

        if (!$user) {
            $this->addFlash('error', 'Partenaire non trouvé...');

            return $this->redirectToRoute('partner_index');
        }
        $customers = $partnerCustomerRepository->findByPartnerAndProfile($user->getId(), $profile->getId(), null, null, '', '', '');
        $projectChilds = $this->projectChildRepository->findBy(['user' => $user->getId()]);

        $totalTrees = $totalAvailableTrees = $totalUnavailableTrees = 0;
        if (!empty($projectChilds)) {
            $parcel_ids = [];
            foreach ($projectChilds as $projectChild) {
                $parcels = $this->parcelRepository->findBy(['projectChild' => $projectChild]);
                foreach ($parcels as $parcel) {
                    $parcel_ids[] = $parcel->getId();
                }
            }
            if (!empty($parcel_ids)) {
                $treeRepository = $this->entityManager->getRepository(ParcelTreeSpecies::class);
                $parcelTrees = $treeRepository->findBy(['parcel' => $parcel_ids]);
                foreach ($parcelTrees as $parcelTree) {
                    $totalTrees += $parcelTree->getTreeNumber();
                    $totalAvailableTrees += $parcelTree->getAvailableTreeNumber();
                }
                $totalUnavailableTrees = $totalTrees - $totalAvailableTrees;
            }
        }

        return $this->render('admin/user/customer-finals.html.twig', [
            'user' => $user,
            'totalTrees' => $totalTrees,
            'totalAvailableTrees' => $totalAvailableTrees,
            'totalUnavailableTrees' => $totalUnavailableTrees,
            'dataCustomers' => ['partner' => $user, 'customers' => $customers]
        ]);
    }

    #[Route('/customer-finals-export-excel', name: 'customer_finals_export_excel', methods: ['GET'])]
    public function customerExport(Request $request, ProfileRepository $profile, PartnerService $partnerService): JsonResponse
    {
        // Récupération des données
        $response = false;
        $params = $request->query->all();
        $partnerId = array_key_exists('partnerId', $params) && !empty($params['partnerId']) ? intval($params['partnerId']) : 0;
        $search = array_key_exists('search', $params) && !empty($params['search']) ? $params['search'] : '';
        $dateStart = array_key_exists('dateStart', $params) && !empty($params['dateStart'])
            ? date_create_from_format(self::DATE_FORMAT, $params['dateStart'])->setTime(0, 0, 0)
            : null;

        $dateEnd = array_key_exists('dateEnd', $params) && !empty($params['dateEnd'])
            ? date_create_from_format(self::DATE_FORMAT, $params['dateEnd'])->setTime(23, 59, 59)
            : null;
        $excel = '';

        $partner = $this->entityManager->getRepository(User::class)->find($partnerId);
        $profile = $profile->getProfileByCode('Client');

        if ($partner && $profile) {
            $data = $partnerService->getDataFinalCustomers($partnerId, $profile, $dateStart, $dateEnd, $search);
            if ($data) {
                $excel = $this->exportExcel->exportToExcel($data, $partner->getLastName() . '_' . $partner->getFirstName());
                $response = true;
            }
        }

        return new JsonResponse(['success' => $response, 'data' => $excel]);
    }

    #[Route('/{id}/customer-finals-ajax', name: 'customer_finals_ajax', methods: ['GET'])]
    public function listAjaxFinalCustomers(int $id, Request $request, PartnerCustomerRepository $partnerCustomerRepository, ProfileRepository $profile): JsonResponse
    {
        $params = $request->query->all();
        // Pagination parameters
        $draw = (int)$request->query->get('draw', 1);
        $start = (int)($params['start'] ?? 0);
        $length = (int)($params['length'] ?? 10);
        $search = isset($params['search']) && isset($params['search']['value']) ? $params['search']['value'] : '';
        $partnerId = max($id, 0);
        $dateStart = array_key_exists('dateStart', $params) && !empty($params['dateStart'])
            ? date_create_from_format(self::DATE_FORMAT, $params['dateStart'])->setTime(0, 0, 0)
            : null;

        $dateEnd = array_key_exists('dateEnd', $params) && !empty($params['dateEnd'])
            ? date_create_from_format(self::DATE_FORMAT, $params['dateEnd'])->setTime(23, 59, 59)
            : null;

        $profile = $profile->getProfileByCode('Client');
        $customers = [];
        $totalCount = $filteredCount = 0;
        if ($partnerId > 0 && $profile) {
            $customers = $partnerCustomerRepository->findByPartnerAndProfile($partnerId, $profile->getId(), $start, $length, $dateStart, $dateEnd, $search);
            $totalCount = $partnerCustomerRepository->countAllPartnerAndProfile($partnerId, $profile->getId(), $dateStart, $dateEnd);
            $filteredCount = $partnerCustomerRepository->countAllFilteredPartnerAndProfile($partnerId, $profile->getId(), $dateStart, $dateEnd, $search);
        }

        $data = [
            'draw' => $draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $filteredCount,
            'data' => array_map(function ($customer) {
                return $customer;
            }, $customers),
        ];

        return new JsonResponse($data);
    }

    #[Route('/{id}/customer-finals-export-qrcode', name: 'customer_finals_export_qrcode', methods: ['GET'])]
    public function exportQrCode(?int $id, PartnerService $partnerService, QrCodeService $qrCodeService): JsonResponse
    {
        $response = false;
        $fileUrl = array();
        $data = $partnerService->exportQrCodeByCustomer($id);
        if ($data) {
            foreach ($data as $value) {
                $fileUrl[] = $qrCodeService->generateQrCode($value['qrCode'], 'png');
            }
            $response = true;
        }

        return new JsonResponse([
            'status' => $response,
            'fileUrls' => $fileUrl
        ]);
    }

    #[Route('/{id}/certificate-report', name: 'user_certificate_report', methods: ['GET'])]
    public function showCertificateAndReport(User $user): Response
    {
        $certificates = $this->entityManager->getRepository(Certificate::class)->findBy(['user' => $user], ['createdAt' => 'DESC']);
        $reportHistories = $this->entityManager->getRepository(ReportHistory::class)->findBy(['user' => $user], ['createdAt' => 'DESC']);

        foreach ($certificates as $certificate) {
            $this->setFileDetails($certificate);
        }

        foreach ($reportHistories as $reportHistory) {
            $this->setFileDetails($reportHistory);
        }

        return $this->render('admin/user/certificate-report.html.twig', [
            'user' => $user,
            'certificates' => $certificates,
            'reportHistories' => $reportHistories,
        ]);
    }

    private function setFileDetails($entity): void
    {
        $filePath = $entity->getFilePath();
        if (file_exists($filePath)) {
            $fileSize = filesize($filePath);
            $fileSizeMB = number_format($fileSize / 1048576, 2) . ' MB';
            $entity->setFileSizeMB($fileSizeMB);
        }
    }

    #[Route('/{user}/option/update', name: 'user_option_update', methods: ['POST'])]
    public function updateUserOption(User $user, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données envoyées en AJAX
        $data = json_decode($request->getContent(), true);

        if (!isset($data['optionName']) || !array_key_exists('optionValue', $data)) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        $optionName = $data['optionName'];
        $optionValue = $data['optionValue']; // On garde la valeur brute et on la traite après

        // 🔹 Vérifier si l'option existe déjà
        $option = $user->getOptions()->filter(fn(UserOption $opt) => $opt->getName() === $optionName)->first();

        if (!$option) {
            // 🔹 Créer l'option si elle n'existe pas encore
            $option = new UserOption();
            $option->setUser($user);
            $option->setName($optionName);
            $entityManager->persist($option);
        }

        // 🔹 Déterminer dynamiquement le type de la valeur
        if (is_numeric($optionValue)) {
            $optionValue = strpos($optionValue, '.') !== false ? (float)$optionValue : (int)$optionValue;
        } elseif ($optionValue === "true" || $optionValue === "false") {
            $optionValue = filter_var($optionValue, FILTER_VALIDATE_BOOLEAN);
        }

        // 🔹 Vérifier si la valeur est identique pour éviter des mises à jour inutiles
        if ($option->getValue() === $optionValue) {
            return new JsonResponse(['success' => true, 'message' => 'Aucune modification nécessaire']);
        }

        // 🔹 Mettre à jour l'option utilisateur
        $option->setValue($optionValue);

        // 🔹 Sauvegarder en base de données
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'option' => $optionName,
            'value' => $optionValue,
            'message' => 'Option mise à jour avec succès'
        ]);
    }

    #[Route('/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function editInfosUser(Request $request, Utils $utils): Response
    {
        $currentUser = $this->getUser();

        if (!$utils->isAdmin($currentUser)) {
            return $this->json([
                'success' => false,
                'message' => 'Accès refusé. Vous n\'avez pas les droits nécessaires pour effectuer cette action.',
            ]);
        }

        $userId = (int) $request->get('id', 0);
        $firstName = trim((string) $request->get('firstname', ''));
        $lastName = trim((string) $request->get('lastname', ''));
        $email = trim((string) $request->get('useremail', ''));
        $userPassword = $request->get('user_password');
        $confirmPassword = $request->get('user_confirm_password');

        if ($userId === 0 || empty($firstName) || empty($lastName) || empty($email)) {
            return $this->json([
                'success' => false,
                'message' => 'Les champs obligatoires sont manquants.',
            ]);
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'L\'utilisateur n\'existe pas.',
            ]);
        }

        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        if ( !empty($userPassword) || !empty($confirmPassword)) {
            if ($userPassword === $confirmPassword && $userPassword !== '') {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $userPassword);
                $user->setPassword($hashedPassword);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Les mots de passe ne correspondent pas.',
                ]);
            }
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès.',
        ]);
    }

}
