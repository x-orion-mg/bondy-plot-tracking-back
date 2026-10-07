<?php

namespace App\Controller\Admin;

use App\Entity\UsageType;
use App\Utils\PathName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ServiceUploadFiles;
use function PHPUnit\Framework\isNull;

#[Route('/admin/usage-type')]
class UsageTypeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceUploadFiles $serviceUploadFiles
    )
    {
        $this->entityManager      = $entityManager;
        $this->serviceUploadFiles = $serviceUploadFiles;
    }

    #[Route(name: 'usage_type_index', methods: ['GET'])]
    public function index(): Response
    {
        $usageTypes = $this->entityManager
                ->getRepository(UsageType::class)
                ->findBy([], ['id' => 'DESC']);

        return $this->render('admin/usage-type/index.html.twig', [
            'usageTypes' => $usageTypes,
        ]);
    }

    #[Route('/edit', name: 'usage_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $response = false;
        $params   = $request->request->all();
        $files    = $request->files->all();
        $id       = array_key_exists('id', $params) && !empty($params['id']) ? intval($params['id']) : 0;
        $name     = array_key_exists('label', $params) && !empty($params['label']) ? $params['label'] : '';
        $icon     = array_key_exists('iconFile', $files) && !empty($files['iconFile']) ? $files['iconFile'] : '';

        $existingUsageType = $this->entityManager->getRepository(UsageType::class)->findOneBy(['id' => $id]);
        $usageType         = $existingUsageType ?: new UsageType();

        if (!empty($name) && $usageType) {
            if ( $id <= 0 || isNull($id) ) {
                $duplicateUsageType = $this->entityManager->getRepository(UsageType::class)->findOneBy(['label' => $name]);
                if ($duplicateUsageType) {
                    return $this->json(['success' => false]);
                }
            }

            if ($files && $icon) {
                $fileUrl = $this->serviceUploadFiles->uploadIcon(PathName::UPLOAD_ICON_USAGE_TYPE, $files);
                $usageType->setIcon($fileUrl);
            }
            $usageType->setLabel($name);
            $this->entityManager->persist($usageType);
            $this->entityManager->flush();
            $response = true;
        }

        return $this->json(['success' => $response]);
    }

    #[Route('/{id}/delete', name: 'usage_type_delete', methods: ['DELETE'])]
    public function delete(?int $id): Response
    {
        $response   = false;
        $usageType = $this->entityManager
            ->getRepository(UsageType::class)
            ->findOneBy([ 'id' => $id ]);

		if ($usageType) {
            $this->entityManager->remove($usageType);
            $this->entityManager->flush();
            $response = true;
		}

		return $this->json(['success' => $response], Response::HTTP_OK);
    }
}
