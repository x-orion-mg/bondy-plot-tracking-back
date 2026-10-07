<?php

namespace App\Controller\Api;

use App\Entity\Parcel;
use App\Entity\User;
use App\Service\TreeSpeciesService;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route( '/api/v1/dashboard' )]
class ApiDashboardController extends AbstractApiController {
	private const USER_NOT_FOUND = 'User not found';

	public function __construct(
		SerializerInterface $serializer,
		private readonly EntityManagerInterface $entityManager,
	) {
		parent::__construct( $serializer );
	}

	#[Route( '/{uuid}/user', name: 'api_user_trees_per_species', methods: [ 'GET' ] )]
	public function dashboard( string $uuid, TreeSpeciesService $treeSpeciesService , ValidatorInterface $validator): Response {
		if (!Uuid::isValid($uuid)) {
			return $this->createErrorResponse('Invalid UUID format', Response::HTTP_BAD_REQUEST);
		}
		$user = $this->entityManager->getRepository( User::class )->findOneBy( [ 'uuid' => $uuid ] );

		if ( ! $user ) {
			return $this->createErrorResponse( self::USER_NOT_FOUND, Response::HTTP_NOT_FOUND );
		}

		$projectChildren                           = $user->getProjectChildren();
		$totalRestoredHectares                     = 0;
		$totalIndirectBeneficiaries                = 0;
		$totalSeasonalJobsAndSustainableEmployment = 0;
		$TCO2Sequestered                           = [];
		$currentYear                               = (int) date( 'Y' );

		// Récupération de toutes les parcelles en une seule requête
		$parcels = $this->entityManager->getRepository( Parcel::class )->findBy( [
			'projectChild' => $projectChildren->toArray()
		] );

		$projectDetails = array_map( function ( $projectChild ) use ( &$totalRestoredHectares, &$totalIndirectBeneficiaries, &$totalSeasonalJobsAndSustainableEmployment, &$TCO2Sequestered, $currentYear ) {
			$totalRestoredHectares                     += (float) $projectChild->getEconomicImpactRestoredHectares();
			$totalIndirectBeneficiaries                += (int) $projectChild->getSocialImpactIndirectBeneficiaries();
			$totalSeasonalJobsAndSustainableEmployment += (int) $projectChild->getSocialImpactSeasonalJobs() + (int) $projectChild->getEconomicImpactSustainableEmployment();

			if ( $startDate = $projectChild->getStartDate() ) {
				$year = (int) $startDate->format( 'Y' );
				if ( $year >= $currentYear - 20 ) {
					$TCO2Sequestered[ $year ] = ( $TCO2Sequestered[ $year ] ?? 0 ) + (float) $projectChild->getEnvironmentalImpactCO2();
				}
			}

			return [
				'projectChildName'      => $projectChild->getTitle(),
				'projectStepPercentage' => $projectChild->getProjectStep()->getPercentage(),
			];
		}, $projectChildren->toArray() );

		$TCO2SequesteredFormatted = array_map( fn( $year, $value ) => [ 'date'  => $year,
		                                                                'value' => $value
		], array_keys( $TCO2Sequestered ), $TCO2Sequestered );

		// Gestion des espèces d'arbres
		$treeSpecies     = $treeSpeciesService->aggregateTreeSpecies( $parcels );
		$treeSpeciesData = $treeSpeciesService->getTreeSpeciesDashboardData( $treeSpecies );
		$totalTreeCount  = array_sum( array_column( $treeSpeciesData, 'treeCount' ) );

		// Gestion des certificats et rapports
		$certificates      = $user->getCertificates();
		$isCertificate     = $user->getOptionValue( 'dashboard_certificate' ) && ! $certificates->isEmpty();
		$latestCertificate = $isCertificate ? $certificates->last() : null;

		$reportHistories     = $user->getReportHistories();
		$latestReportHistory = ! $reportHistories->isEmpty() ? $reportHistories->last() : null;

		$data = [
			'user'            => $user,
			'projectStatus'   => $projectDetails,
			'treeSpecies'     => $treeSpeciesData,
			'totalTreeCount'  => $totalTreeCount,
			'keyNumber'       => [
				'totalProjectChild'                         => count( $projectChildren ),
				'totalRestoredHectares'                     => $totalRestoredHectares,
				'totalIndirectBeneficiaries'                => $totalIndirectBeneficiaries,
				'totalSeasonalJobsAndSustainableEmployment' => $totalSeasonalJobsAndSustainableEmployment,
			],
			'TCO2Sequestered' => $TCO2SequesteredFormatted,
			'isCertificate'   => $latestCertificate !== null,
			'isRapport'       => $latestReportHistory !== null,
		];

		return $this->createSuccessResponse( $data, [ 'user', 'tree_species_dashboard' ] );
	}
}
