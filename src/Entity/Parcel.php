<?php

namespace App\Entity;

use App\Entity\Traits\Identifiable;
use App\Entity\Traits\TimeTrackable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Parcel {
	use TimeTrackable;
	use Identifiable;

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups( [ 'parcel', 'parcel_list', 'parcel_show','parcel_trees', 'parcel_tree' ] )]
	private ?int $id = null;

	#[ORM\ManyToOne( targetEntity: ProjectChild::class, inversedBy: 'parcels' )]
	#[ORM\JoinColumn( nullable: false )]
	#[Groups( [ 'parcel' ] )]
	private ?ProjectChild $projectChild = null;

	#[ORM\Column( type: Types::TEXT )]
	private ?string $delimitation = null;

	#[ORM\Column( type: Types::STRING )]
	#[Groups( [ 'parcel', 'parcel_list', 'parcel_show' ] )]
	private ?string $title = null;

    #[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
    private ?string $tiffImage = null;

    #[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
    #[Groups( [ 'parcel', 'parcel_show' ] )]
    private ?string $tiffUrl = null;

    #[ORM\Column( type: Types::JSON, nullable: true )]
    #[Groups( [ 'parcel', 'parcel_show' ] )]
    private ?array $tiffCoordinates = null;

	#[ORM\Column( type: Types::DATETIME_MUTABLE )]
	#[Groups( [ 'parcel', 'parcel_show' ] )]
	private ?\DateTime $startDate = null;

	#[ORM\Column( type: Types::DATETIME_MUTABLE )]
	#[Groups( [ 'parcel', 'parcel_show' ] )]
	private ?\DateTime $plantingYear = null;

	#[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
	#[Groups( [ 'parcel', 'parcel_list', 'parcel_show' ] )]
	private ?string $city = null;

	#[ORM\Column( type: Types::DECIMAL, precision: 16, scale: 6 )]
	#[Groups( [ 'parcel', 'parcel_list', 'parcel_show' ] )]
	private ?string $latitude;

	#[ORM\Column( type: Types::DECIMAL, precision: 16, scale: 6 )]
	#[Groups( [ 'parcel', 'parcel_list', 'parcel_show' ] )]
	private ?string $longitude;

	#[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
	#[Groups( [ 'parcel', 'parcel_show' ] )]
	private ?string $bannerImage = null;

	#[ORM\Column( type: Types::JSON, nullable: true )]
	#[Groups( [ 'parcel' ] )]
	private ?string $trees = null;

	#[ORM\OneToMany( targetEntity: ParcelTreeSpecies::class, mappedBy: 'parcel', cascade: [ 'persist', 'remove' ] )]
	private Collection $parcelTreeSpecies;

	#[ORM\ManyToOne( targetEntity: City::class )]
	#[ORM\JoinColumn( nullable: true )]
	private City $town;

	#[Groups( [ 'parcel', 'parcel_list', 'parcel_show','parcel_tree' ] )]
	private ?array $delimitationJson = null;

	#[Groups( [ 'parcel_trees', 'parcel_show' ] )]
	private ?array $treesJson = null;

	#[Groups( [ 'parcel', 'parcel_list', 'parcel_show' ] )]
	private ?string $startDateFormatted = null;
	#[Groups( [ 'parcel', 'parcel_list', 'parcel_show', 'parcel_tree' ] )]
	private ?Integer $treeAge = null;

	public function __construct() {
		$this->createdAt         = new \DateTime();
		$this->parcelTreeSpecies = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getProjectChild(): ?ProjectChild {
		return $this->projectChild;
	}

	public function setProjectChild( ?ProjectChild $projectChild ): self {
		$this->projectChild = $projectChild;

		return $this;
	}

	public function getDelimitation(): ?string {
		return $this->delimitation;
	}

	public function setDelimitation( string $delimitation ): self {
		$this->delimitation = $delimitation;

		return $this;
	}

	public function getTiffImage(): ?string {
		return $this->tiffImage;
	}

	public function setTiffImage( string $tiffImage ): self {
		$this->tiffImage = $tiffImage;

		return $this;
	}

    public function getTiffCoordinates(): ?array {
        return $this->tiffCoordinates;
    }

    public function setTiffCoordinates( ?array $tiffCoordinates ): self {
        $this->tiffCoordinates = $tiffCoordinates;

        return $this;
    }

	/**
	 * @return Collection<int, ParcelTreeSpecies>
	 */
	public function getParcelTreeSpecies(): Collection {
		return $this->parcelTreeSpecies;
	}

	public function addParcelTreeSpecies( ParcelTreeSpecies $parcelTreeSpecies ): self {
		if ( ! $this->parcelTreeSpecies->contains( $parcelTreeSpecies ) ) {
			$this->parcelTreeSpecies[] = $parcelTreeSpecies;
			$parcelTreeSpecies->setParcel( $this );
		}

		return $this;
	}

	public function removeParcelTreeSpecies( ParcelTreeSpecies $parcelTreeSpecies ): self {
		if ( $this->parcelTreeSpecies->removeElement( $parcelTreeSpecies ) && $parcelTreeSpecies->getParcel() === $this ) {
			$parcelTreeSpecies->setParcel( null );
		}

		return $this;
	}

	public function getTitle(): ?string {
		return $this->title;
	}

	public function setTitle( ?string $title ): void {
		$this->title = $title;
	}

	public function getStartDate(): ?\DateTime {
		return $this->startDate;
	}

	public function setStartDate( ?\DateTime $startDate ): void {
		$this->startDate = $startDate;
	}

	public function getPlantingYear(): ?\DateTime {
		return $this->plantingYear;
	}

	public function setPlantingYear( ?\DateTime $plantingYear ): void {
		$this->plantingYear = $plantingYear;
	}

	public function getCity(): ?string {
		return $this->city;
	}

	public function setCity( ?string $city ): void {
		$this->city = $city;
	}

	public function getLatitude(): ?float {
		return $this->latitude;
	}

	public function setLatitude( ?string $latitude ): void {
		$this->latitude = $latitude;
	}

	public function getLongitude(): ?float {
		return $this->longitude;
	}

	public function setLongitude( ?string $longitude ): void {
		$this->longitude = $longitude;
	}

	public function getBannerImage(): ?string {
		return $this->bannerImage;
	}

	public function setBannerImage( ?string $bannerImage ): void {
		$this->bannerImage = $bannerImage;
	}

	public function getDelimitationJson(): ?array {
		$this->delimitationJson = json_decode( $this->delimitation, true );

		return $this->delimitationJson;
	}

	public function setDelimitationJson( ?string $delimitationJson ): void {
		$this->delimitationJson = $delimitationJson;
	}

	public function getTreesJson(): ?array {
		$this->treesJson = json_decode( $this->trees, true );

		return $this->treesJson;
	}

	public function setTreesJson(): void {
		$this->treesJson = json_decode( $this->trees, true );
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

	public function getTown(): City {
		return $this->town;
	}

	public function setTown( City $town ): void {
		$this->town = $town;
	}

	public function getTrees(): ?string {
		return $this->trees;
	}

	public function setTrees( ?string $trees ): void {
		$this->trees = $trees;
	}

	public function getTreeAge(): ?int {
		if ($this->plantingYear) {
			$currentYear = (int) (new \DateTime())->format('Y');
			$plantingYear = (int) $this->plantingYear->format('Y');
			$age = $currentYear - $plantingYear;
			return (string) ($age === 0 ? -1 : $age);
		}
		return -1;
	}

	public function setTreeAge( ?int $treeAge ): void {
		$this->treeAge = $treeAge;
	}

    public function getTiffUrl(): ?string
    {
        return $this->tiffUrl;
    }

    public function setTiffUrl(?string $tiffUrl): void
    {
        $this->tiffUrl = $tiffUrl;
    }


}
