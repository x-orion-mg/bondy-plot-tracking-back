<?php

namespace App\Service;

use App\Entity\AllocatedTree;
use App\Entity\Parcel;
use App\Entity\Partner;
use App\Entity\PartnerCustomer;
use App\Entity\ProjectChild;
use App\Entity\User;
use App\Repository\ParcelRepository;
use App\Repository\PartnerCustomerRepository;
use App\Repository\AllocatedTreeRepository;
use App\Service\QrCodeService;
use Doctrine\ORM\EntityManagerInterface;

class PartnerService
{
	private EntityManagerInterface $entityManager;
    private PartnerCustomerRepository $partnerCustomerRepository;
    private ParcelRepository $parcelRepository;
    private AllocatedTreeRepository $allocatedTreeRepository;
    private QrCodeService $qrCodeService;

    public function __construct(
        EntityManagerInterface    $entityManager,
        PartnerCustomerRepository $partnerCustomerRepository,
        ParcelRepository          $parcelRepository,
        AllocatedTreeRepository   $allocatedTreeRepository,
        QrCodeService             $qrCodeService)
    {
        $this->entityManager = $entityManager;
        $this->partnerCustomerRepository = $partnerCustomerRepository;
        $this->parcelRepository = $parcelRepository;
        $this->allocatedTreeRepository = $allocatedTreeRepository;
        $this->qrCodeService = $qrCodeService;
    }

	public function deletePartner(User $partner): void
	{
		$this->deletePartnerCustomers($partner);
		$this->deleteProjectChildren($partner);
		$this->entityManager->remove($partner);
		$this->entityManager->flush();
	}

	private function deletePartnerCustomers(User $partner): void
	{
		$partnerCustomers = $this->entityManager->getRepository(PartnerCustomer::class)->findBy(['partner' => $partner]);
		foreach ($partnerCustomers as $partnerCustomer) {
			$this->entityManager->remove($partnerCustomer);
		}
	}

	private function deleteProjectChildren(User $partner): void
	{
		$projectChildren = $this->entityManager->getRepository(ProjectChild::class)->findBy(['user' => $partner]);
		foreach ($projectChildren as $projectChild) {
			$this->deleteParcels($projectChild);
			$this->entityManager->remove($projectChild);
		}
	}

	private function deleteParcels(ProjectChild $projectChild): void
	{
		$parcels = $this->entityManager->getRepository(Parcel::class)->findBy(['projectChild' => $projectChild]);
		foreach ($parcels as $parcel) {
			$this->deleteAllocatedTrees($parcel);
			$this->entityManager->remove($parcel);
		}
	}

	private function deleteAllocatedTrees(Parcel $parcel): void
	{
		$allocatedTrees = $this->entityManager->getRepository(AllocatedTree::class)->findBy(['parcel' => $parcel]);
		foreach ($allocatedTrees as $allocatedTree) {
			$this->entityManager->remove($allocatedTree);
		}
	}

    public function getDataFinalCustomers(?int $partnerId, $profile, $dateStart, $dateEnd, $search): array
    {
        $results = [];
        $parcelsIds = $this->parcelRepository->findParcelsIdsByPartnerId($partnerId);
        $customers  = $this->partnerCustomerRepository->findByPartnerAndProfile($partnerId, $profile->getId(),  null, null, $dateStart, $dateEnd, $search);
        if ($customers){
            foreach ($customers as $customer) {
                $treeAllocates = $this->allocatedTreeRepository->findAllocatedTreeByUserId( $customer['userId'], $parcelsIds );
                if ($treeAllocates){
                    foreach ($treeAllocates as $treeAllocate) {
                        $qrCodeUrl = $this->qrCodeService->generateFrontendUrl($customer['uuid'] .'/'.$treeAllocate->getTrackCode());
                        $qrCode64 = $this->qrCodeService->generateQrCode($qrCodeUrl, 'base64');
                        $customer['track-code'] = $treeAllocate->getTrackCode();
                        $customer['tree-species'] = $treeAllocate->getTreeSpecies()->getCommonName();
                        $customer['qrCode'] = $qrCode64 && $qrCode64->getContent() ? $qrCode64->getContent() : $qrCodeUrl;

                        $results[] = $customer;
                    }
                }
            }
        }
        return $results;
    }

    /**
     * @param int|null $idCustomer
     * @return array
     */
    public function exportQrCodeByCustomer( ?int $idCustomer ): array
    {
        $results = [];
        $customer = $this->entityManager->getRepository(PartnerCustomer::class)->findOneBy(['user' => $idCustomer]);
        if ($customer){
            $parcelsIds = $this->parcelRepository->findParcelsIdsByPartnerId($customer->getPartner()->getId());
            $treeAllocates = $this->allocatedTreeRepository->findAllocatedTreeByUserId( $customer->getUser()->getId(), $parcelsIds );
            if ($treeAllocates){
                foreach ($treeAllocates as $treeAllocate) {
                    $qrCodeUrl = $this->qrCodeService->generateFrontendUrl($customer->getUser()->getUuid() .'/'.$treeAllocate->getTrackCode());
                    $results[] = [
                        'track-code' => $treeAllocate->getTrackCode(),
                        'tree-species' => $treeAllocate->getTreeSpecies()->getCommonName(),
                        'qrCode' => $qrCodeUrl
                    ];
                }
            }
        }
        return $results;
    }

    public function findPartnerLink( ?int $userId )
    {
        $dataPartner = null;
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);
        if ($user){
            $dataPartner = $this->entityManager->getRepository(Partner::class)->getPartnerByEmail($user->getEmail());
        }
        return $dataPartner;
    }
}
