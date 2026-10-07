<?php

namespace App\Service;

use App\Entity\AllocatedTree;
use App\Entity\CustomerImport;
use App\Entity\Parcel;
use App\Entity\ParcelTreeSpecies;
use App\Entity\PartnerCustomer;
use App\Entity\Profile;
use App\Entity\ProjectChild;
use App\Entity\User;
use App\Exception\CustomException;
use App\Service\FileService;
use App\Utils\PathName;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Asset\Packages;


class CustomerImportService {
	private EntityManagerInterface $entityManager;
	private FileService $fileService;
	private Packages $assetsManager;

	public function __construct( EntityManagerInterface $entityManager, FileService $fileService, Packages $assetsManager ) {
		$this->entityManager = $entityManager;
		$this->fileService   = $fileService;
		$this->assetsManager = $assetsManager;
	}

	public function isFileAlreadyProcessed( string $fileName ): bool {
		return (bool) $this->entityManager->getRepository( CustomerImport::class )->findOneBy( [
			'fileName' => $fileName
		] );
	}

	public function createCustomerImport( string $fileName, int $customerNumbers ): CustomerImport {
		$customerImport = new CustomerImport();
		$customerImport->setFileName( $fileName );
		$customerImport->setCustomerNumbers( $customerNumbers );
		$customerImport->setReportFileName( '' );

		$this->entityManager->persist( $customerImport );
		$this->entityManager->flush();

		return $customerImport;
	}

	/**
	 * @throws \Exception
	 */
	public function allocateTreesToUsers( int $userId, array $fileContent, array $headers ): array {
		$projectChildren = $this->entityManager->getRepository( ProjectChild::class )->findBy( [ 'user' => $userId ] );

		// Définition du dossier et du fichier
		$directoryPath = PathName::UPLOAD_CSV_CUSTOMER_REPORT_IMPORT;

		// Vérifie si le dossier existe, sinon le créer
		if ( ! is_dir( $directoryPath ) && ! mkdir( $directoryPath, 0775, true ) && ! is_dir( $directoryPath ) ) {
			throw new CustomException( "Impossible de créer le répertoire : " . $directoryPath );
		}

		// Chemin du fichier à créer
		$newFileName = 'import_result_' . date( 'Ymd_His' ) . '.csv';
		$newFilePath = $directoryPath . $newFileName;

		// Ouverture du fichier en mode écriture
		$newFile = fopen( $newFilePath, 'w' );

		if ( ! $newFile ) {
			throw new CustomException( "Impossible de créer le fichier : " . $newFilePath );
		}

		// Écriture des en-têtes dans le fichier CSV
		fputcsv( $newFile, array_merge( $headers, [ 'status', 'info' ] ), ';' );

		$treeSpeciesList = $this->getTreeSpeciesList( $projectChildren );
		$okCount         = 0;
		$koCount         = 0;
		$users           = [];

		foreach ( $fileContent as $index => $row ) {
			if ( $index === 0 ) {
				continue; // Ignorer la ligne d'en-tête
			}

			$data = array_combine( $headers, $row );

			list( $status, $info ) = $this->allocateTreeIfPossible( $treeSpeciesList, $data, $userId );
			fputcsv( $newFile, array_merge( $row, [ $status, $info ] ), ';' );

			if ( $status === 'OK' ) {
				$okCount ++;
				$users[] = $this->findOrCreateUser( $data, $userId, $status, $info );
			} else {
				$koCount ++;
			}
		}

		fclose( $newFile );
		$this->entityManager->flush();

		return [ $newFilePath, $okCount, $koCount, $users ];
	}


	private function allocateTreeToUser( User $user, ParcelTreeSpecies $treeSpecies, Parcel $parcel, int $availableTreeNumber ): AllocatedTree {
		$allocatedTree = new AllocatedTree();
		$allocatedTree->setUser( $user );
		$allocatedTree->setTreeSpecies( $treeSpecies->getTreeSpecies() );
		$allocatedTree->setParcel( $parcel );
		$utils              = new Utils();
		$formattedIdCounter = sprintf( '%05d', $availableTreeNumber );
		$trackCode          = $utils->generateTrackCode( $parcel ) . $formattedIdCounter;
		$allocatedTree->setTrackCode( $trackCode );

		$this->entityManager->persist( $allocatedTree );

		$treeSpecies->setAvailableTreeNumber( $treeSpecies->getAvailableTreeNumber() - 1 );
		$this->entityManager->persist( $treeSpecies );

		return $allocatedTree;
	}

	private function getTreeSpeciesList( array $projectChildren ): array {
		$treeSpeciesList = [];

		foreach ( $projectChildren as $projectChild ) {
			$parcels = $this->entityManager->getRepository( Parcel::class )->findBy( [ 'projectChild' => $projectChild ] );

			foreach ( $parcels as $parcel ) {
				$parcelTreeSpecies = $this->entityManager->getRepository( ParcelTreeSpecies::class )->findBy( [ 'parcel' => $parcel ] );

				foreach ( $parcelTreeSpecies as $treeSpecies ) {
					$treeSpeciesList[] = [ 'treeSpecies' => $treeSpecies, 'parcel' => $parcel ];
				}
			}
		}

		return $treeSpeciesList;
	}

	private function allocateTreeIfPossible( array $treeSpeciesList, array $data, int $userId ): array {
		$status = 'KO';
		$info   = 'Not allocated';

		if ( empty( $data['nom'] ) ) {
			$info = 'Name is empty';

			return [ $status, $info ];
		}

		foreach ( $treeSpeciesList as $item ) {
			if ( $this->canAllocateTree( $item['treeSpecies'], $data ) ) {
				$user = $this->findOrCreateUser( $data, $userId, $status, $info );
				if ( $status === 'KO' ) {
					return [ $status, $info ];
				}
				$allocatedTree = $this->allocateTreeToUser( $user, $item['treeSpecies'], $item['parcel'], $item['treeSpecies']->getAvailableTreeNumber() );
				$status        = 'OK';
				$info          = $this->generateAllocationInfo( $allocatedTree, $item['parcel'] );
				break;
			} else {
				$info = 'No available trees';
			}
		}

		return [ $status, $info ];
	}

	private function findOrCreateUser( array $data, int $userId, string &$status, string &$info ): ?User {
		$user = null;

		if ( ! empty( $data['email'] ) ) {
			if ( filter_var( $data['email'], FILTER_VALIDATE_EMAIL ) ) {
				$user = $this->entityManager->getRepository( User::class )->findOneBy( [ 'email' => $data['email'] ] );
			} else {
				$status = 'KO';
				$info   = 'Invalid email';

				return null;
			}
		}

		if ( ! $user ) {
			if ( empty( $data['nom'] ) ) {
				$status = 'KO';
				$info   = 'Name is empty';

				return null;
			}
			$user = $this->createNewUser( $data, $userId );
		} else {
			$partnerCustomer = $this->entityManager->getRepository( PartnerCustomer::class )->findOneBy( [
				'user'    => $user->getId(),
				'partner' => $userId
			] );

			if ( ! $partnerCustomer ) {
				$partnerCustomer = new PartnerCustomer();
				$partnerCustomer->setUser( $user );
				$partner = $this->entityManager->getRepository( User::class )->find( $userId );
				$partnerCustomer->setPartner( $partner );
				$this->entityManager->persist( $partnerCustomer );
				$this->entityManager->flush();
			}
		}

		$status = 'OK';

		return $user;
	}


	private function canAllocateTree( ParcelTreeSpecies $treeSpecies, array $data ): bool {
		return $treeSpecies->getAvailableTreeNumber() > 0 && ! empty( $data['nom'] );
	}


	private function generateAllocationInfo( AllocatedTree $allocatedTree, Parcel $parcel ): string {
		return 'Allocated id: ' . $allocatedTree->getId() . ' in parcel id: ' . $parcel->getId() . ' with track code: ' . $allocatedTree->getTrackCode();
	}


	private function createNewUser( array $data, int $userId ): User {
		$user = new User();
		if ( ! empty( $data['email'] ) ) {
			$user->setEmail( $data['email'] );
		}
		$user->setLastName( $data['nom'] );
		$user->setPassword( ( new \App\Utils\Utils )->generateToken() );
		$profilePartner = $this->entityManager->getRepository( Profile::class )->findOneBy( [ 'code' => 'CLIENT' ] );
		$user->setProfile( $profilePartner );
		$user->setRoles( [ 'ROLE_CLIENT' ] );

		$this->entityManager->persist( $user );
		$this->entityManager->flush();

		$partnerCustomer = new PartnerCustomer();
		$partnerCustomer->setUser( $user );
		$partner = $this->entityManager->getRepository( User::class )->find( $userId );
		$partnerCustomer->setPartner( $partner );
		$this->entityManager->persist( $partnerCustomer );
		$this->entityManager->flush();

		return $user;
	}

	public function saveCustomerImport( CustomerImport $customerImport, string $reportFileName ): void {
		$customerImport->setReportFileName( $reportFileName );
		$this->entityManager->persist( $customerImport );
		$this->entityManager->flush();
	}

	public function processFile( UploadedFile $file, int $userId, CsvProcessorInterface $csvProcessor, string $schemeAndHttpHost ): array {
		$response = [];
		try {
			$filePath    = $this->fileService->moveFile( $file );
			$fileContent = $csvProcessor->processCsvFile( $filePath );

			if ( $this->fileService->isInvalidFileContent( $fileContent ) ) {
				$response = $this->generateModalResponse(
					'Erreur de fichier',
					'Le fichier CSV est vide ou mal formaté.',
					'bg-danger-gradient'
				);
			} else {
				$headers = str_getcsv( implode( ';', $fileContent[0] ), ';' );
				if ( ! $csvProcessor->validateHeaders( $headers ) ) {
					$response = $this->generateModalResponse(
						'En-têtes de fichier invalides',
						'Le fichier CSV doit contenir les colonnes "email" et "nom".',
						'bg-danger-gradient'
					);
				} else {
					$response = $this->handleValidFile( $file->getClientOriginalName(), $fileContent, $headers, $userId, $schemeAndHttpHost );
				}
			}
		} catch ( \Exception $e ) {
			$response = $this->generateModalResponse(
				'Erreur inattendue',
				$e->getMessage(),
				'bg-danger-gradient'
			);
		}

		return $response;
	}

	/**
	 * @throws \Exception
	 */
	private function handleValidFile( string $fileName, array $fileContent, array $headers, int $userId ): array {
		$customerImport = $this->createCustomerImport( $fileName, count( $fileContent ) - 1 );
		list( $newFilePath, $okCount, $koCount, $users ) = $this->allocateTreesToUsers( $userId, $fileContent, $headers );

		foreach ( $users as $user ) {
			$customerImport->addUser( $user );
		}

		$newFileReportUrl = $this->assetsManager->getUrl( $newFilePath );
		$this->saveCustomerImport( $customerImport, $newFileReportUrl );

		// Déterminer la couleur en fonction des résultats
		$type    = 'bg-danger-gradient'; // Par défaut
		$title   = 'Importation échouée';
		$message = "Les clients n'ont pas été importés. Total : ".($okCount + $koCount).". Veuillez vérifier les erreurs";
		if ( $okCount > 0 && $koCount === 0 ) {
			$type    = 'bg-success-gradient';
			$title   = 'Importation réussie';
			$message = 'Les clients ont été importés avec succès. Aucune erreur détectée.';
		} elseif ( $okCount > 0 && $koCount > 0 ) {
			$title   = 'Importation partielle';
			$type    = 'bg-warning-gradient';
			$message = "Le fichier a été importé partiellement. $okCount lignes ont été traitées avec succès, $koCount ligne a échoué. Total : ".($okCount + $koCount).". Veuillez vérifier les erreurs";
		}

		return $this->generateModalResponse(
			$title,
			$message,
			$type,
			$newFileReportUrl
		);
	}

	public function generateModalResponse( string $title, string $message, string $type, string $downloadUrl = '' ): array {
		$modal = '<div class="modal-header">
                <h5 class="modal-title">' . htmlspecialchars( $title ) . '</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="alert ' . $type . '" role="alert">
                    <h4 class="alert-heading">' . htmlspecialchars( $title ) . '</h4>
                    <p>' . nl2br( htmlspecialchars( $message ) ) . '</p>
                </div>
              </div>';

		if ( $downloadUrl ) {
			$modal .= '<div class="modal-footer">
                      <a href="' . htmlspecialchars( $downloadUrl ) . '" download class="btn btn-primary">
                          Télécharger le rapport d\'importation
                      </a>
                   </div>';
		}

		return [ 'success' => $type === 'success', 'message' => $modal ];
	}

}
