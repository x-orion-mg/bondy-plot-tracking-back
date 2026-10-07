<?php

namespace App\Entity;

use App\Entity\Traits\Identifiable;
use App\Entity\Traits\TimeTrackable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class ProjectChild {
	use TimeTrackable;
	use Identifiable;

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups( [ 'project_child', 'parcel', 'parcel_list' ] )]
	private ?int $id = null;
	#[ORM\Column(type: Types::STRING, length: 50)]
	#[Groups(['project_child'])]
	private ?string $title = null;

	#[ORM\Column( type: 'text', nullable: true )]
	#[Groups( [ 'project_child' ] )]
	private ?string $history = null;

	#[ORM\Column( type: 'string', length: 255, nullable: true )]
	#[Groups( [ 'project_child', 'parcel', 'parcel_list' ] )]
	private ?string $city = null;

	#[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
	#[Groups( [ 'project_child', 'parcel', 'parcel_list' ] )]
	private ?string $bannerImage = null;

	#[ORM\Column( type: Types::DATETIME_MUTABLE )]
	#[Groups( [ 'project_child', 'parcel' ] )]
	private ?\DateTime $startDate = null;

	#[ORM\ManyToOne( targetEntity: ProjectParent::class, inversedBy: 'projectChildren' )]
	#[ORM\JoinColumn( nullable: false )]
	#[Groups( [ 'project_child', 'parcel', 'parcel_list' ] )]
	private ?ProjectParent $projectParent = null;

	#[ORM\ManyToOne( targetEntity: User::class )]
	#[ORM\JoinColumn( nullable: false )]
	#[Groups( [ 'project_child' ] )]
	private ?User $user = null;

	#[ORM\ManyToOne( targetEntity: ProjectStep::class, inversedBy: 'projectChildren' )]
	#[ORM\JoinColumn( nullable: false )]
	#[Groups( [ 'project_child' ] )]
	private ?ProjectStep $projectStep = null;

	#[ORM\Column( type: 'string', length: 255, nullable: true )]
	#[Groups( [ 'project_child' ] )]
	private ?string $environmentalImpactPlantedTrees = null;

	#[ORM\Column( type: 'string', length: 255, nullable: true )]
	#[Groups( [ 'project_child' ] )]
	private ?string $environmentalImpactCO2 = null;

	#[ORM\Column( type: 'string', length: 255, nullable: true )]
	#[Groups( [ 'project_child' ] )]
	private ?string $economicImpactSustainableEmployment = null;

	#[ORM\Column( type: 'string', length: 255, nullable: true )]
	#[Groups( [ 'project_child' ] )]
	private ?string $economicImpactRestoredHectares = null;

	#[ORM\Column( type: 'string', length: 255, nullable: true )]
	#[Groups( [ 'project_child' ] )]
	private ?string $socialImpactSeasonalJobs = null;

	#[ORM\Column( type: 'string', length: 255, nullable: true )]
	#[Groups( [ 'project_child' ] )]
	private ?string $socialImpactIndirectBeneficiaries = null;

	#[Groups( [ 'project_child', 'parcel_list' ] )]
	private ?string $startDateFormatted = null;

	#[ORM\Column( type: Types::DECIMAL, precision: 16, scale: 6 )]
	#[Groups( [ 'project_child', 'localisation' ] )]
	private ?string $latitude;

	#[ORM\Column( type: Types::DECIMAL, precision: 16, scale: 6 )]
	#[Groups( [ 'project_child', 'localisation' ] )]
	private ?string $longitude;

	#[ORM\Column( type: Types::BOOLEAN, options: [ "default" => false ] )]
	private bool $sendNotifications = false;

	#[ORM\OneToMany( targetEntity: ImpactProject::class, mappedBy: 'projectChild', cascade: [ 'persist', 'remove' ] )]
	private Collection $impactProjects;

	#[ORM\ManyToOne( targetEntity: City::class )]
	#[ORM\JoinColumn( nullable: true )]
	private City $town;

    #[ORM\Column(nullable: true)]
    #[Groups( [ 'project_child' ] )]
    private ?int $socialImpactPartnerFarmer = null;

	public function __construct() {
		$this->createdAt = new \DateTime();
		$this->impactProjects = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getHistory(): ?string {
		return $this->history;
	}

	public function setHistory( ?string $history ): self {
		$this->history = $history;

		return $this;
	}

	public function getCity(): ?string {
		return $this->city;
	}

	public function setCity( ?string $city ): self {
		$this->city = $city;

		return $this;
	}

	public function getBannerImage(): ?string {
		return $this->bannerImage;
	}

	public function setBannerImage( ?string $bannerImage ): static {
		$this->bannerImage = $bannerImage;

		return $this;
	}

	public function getProjectParent(): ?ProjectParent {
		return $this->projectParent;
	}

	public function setProjectParent( ?ProjectParent $projectParent ): self {
		$this->projectParent = $projectParent;

		return $this;
	}

	public function getProjectStep(): ?ProjectStep {
		return $this->projectStep;
	}

	public function setProjectStep( ?ProjectStep $projectStep ): self {
		$this->projectStep = $projectStep;

		return $this;
	}

	public function getEnvironmentalImpactPlantedTrees(): ?string {
		return $this->environmentalImpactPlantedTrees;
	}

	public function setEnvironmentalImpactPlantedTrees( ?string $environmentalImpactPlantedTrees ): self {
		$this->environmentalImpactPlantedTrees = $environmentalImpactPlantedTrees;

		return $this;
	}

	public function getEnvironmentalImpactCO2(): ?string {
		return $this->environmentalImpactCO2;
	}

	public function setEnvironmentalImpactCO2( ?string $environmentalImpactCO2 ): self {
		$this->environmentalImpactCO2 = $environmentalImpactCO2;

		return $this;
	}

	public function getEconomicImpactSustainableEmployment(): ?string {
		return $this->economicImpactSustainableEmployment;
	}

	public function setEconomicImpactSustainableEmployment( ?string $economicImpactSustainableEmployment ): self {
		$this->economicImpactSustainableEmployment = $economicImpactSustainableEmployment;

		return $this;
	}

	public function getEconomicImpactRestoredHectares(): ?string {
		return $this->economicImpactRestoredHectares;
	}

	public function setEconomicImpactRestoredHectares( ?string $economicImpactRestoredHectares ): self {
		$this->economicImpactRestoredHectares = $economicImpactRestoredHectares;

		return $this;
	}

	public function getSocialImpactSeasonalJobs(): ?string {
		return $this->socialImpactSeasonalJobs;
	}

	public function setSocialImpactSeasonalJobs( ?string $socialImpactSeasonalJobs ): self {
		$this->socialImpactSeasonalJobs = $socialImpactSeasonalJobs;

		return $this;
	}

	public function getSocialImpactIndirectBeneficiaries(): ?string {
		return $this->socialImpactIndirectBeneficiaries;
	}

	public function setSocialImpactIndirectBeneficiaries( ?string $socialImpactIndirectBeneficiaries ): self {
		$this->socialImpactIndirectBeneficiaries = $socialImpactIndirectBeneficiaries;

		return $this;
	}

	public function getStartDate(): ?\DateTime {
		return $this->startDate;
	}

	public function setStartDate( \DateTime $startDate ): static {
		$this->startDate = $startDate;
		$this->setStartDateFormatted( $startDate->format( 'M Y' ) );

		return $this;
	}

	public function setStartDateFormatted( ?string $startDateFormatted ): self {
		$this->startDateFormatted = $startDateFormatted;

		return $this;
	}

	public function getStartDateFormatted(): ?string {
		$formatter                = new \IntlDateFormatter(
			'fr_FR',
			\IntlDateFormatter::LONG,
			\IntlDateFormatter::NONE,
			'Europe/Paris',
			\IntlDateFormatter::GREGORIAN,
			'MMMM yyyy'
		);
		$this->startDateFormatted = ucfirst( $formatter->format( $this->startDate ) );

		return $this->startDateFormatted;
	}

	public function getLatitude(): ?float {
		return $this->latitude;
	}

	public function setLatitude( string $latitude ): static {
		$this->latitude = $latitude;

		return $this;
	}

	public function getLongitude(): ?float {
		return $this->longitude;
	}

	public function setLongitude( string $longitude ): static {
		$this->longitude = $longitude;

		return $this;
	}

	public function setSendNotifications( bool $sendNotifications ): self {
		$this->sendNotifications = $sendNotifications;

		return $this;
	}

	public function getSendNotifications(): bool {
		return $this->sendNotifications;
	}

	public function getImpactProjects(): Collection {
		return $this->impactProjects;
	}

	public function setImpactProjects( Collection $impactProjects ): void {
		$this->impactProjects = $impactProjects;
	}

	public function getTown(): City {
		return $this->town;
	}

	public function setTown( City $town ): void {
		$this->town = $town;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser( ?User $user ): self {
		$this->user = $user;

		return $this;
	}

	public function getTitle(): ?string {
		return $this->title;
	}

	public function setTitle( ?string $title ): static {
		$this->title = $title;

		return $this;
	}

    public function getSocialImpactPartnerFarmer(): ?int
    {
        return $this->socialImpactPartnerFarmer;
    }

    public function setSocialImpactPartnerFarmer(?int $socialImpactPartnerFarmer): static
    {
        $this->socialImpactPartnerFarmer = $socialImpactPartnerFarmer;

        return $this;
    }

}
