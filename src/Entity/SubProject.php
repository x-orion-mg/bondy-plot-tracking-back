<?php

namespace App\Entity;

use App\Entity\Traits\CreatedTimeTrackable;
use App\Entity\Traits\Identifiable;
use App\Repository\SubProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity( repositoryClass: SubProjectRepository::class )]
class SubProject {

	use CreatedTimeTrackable;
	use Identifiable;
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[Groups(['sub_project'])]
	private ?int $id = null;

	#[ORM\Column(type: 'string', length: 255)]
	#[Groups(['sub_project'])]
	private string $entityType;

	#[ORM\Column(type: 'integer')]
	#[Groups(['sub_project'])]
	private int $entityId;

	#[ORM\Column(type: 'string', length: 255)]
	#[Groups(['sub_project'])]
	private string $name;

	#[ORM\Column(type: 'string', length: 255)]
	#[Groups(['sub_project'])]
	private string $banner;

	#[ORM\Column(type: Types::DATETIME_MUTABLE)]
	#[Groups(['sub_project'])]
	private ?\DateTime $startDate = null;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['sub_project'])]
	private ?string $city = null;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['sub_project'])]
	private ?string $icon = null;

	#[ORM\Column(type: 'string', length: 255)]
	#[Groups(['sub_project'])]
	private string $historyExcerpt;

	#[ORM\Column(type: Types::TEXT)]
	#[Groups(['sub_project'])]
	private string $historyContent;

	#[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
	#[Groups(['sub_project'])]
	private ?string $latitude = null;

	#[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
	#[Groups(['sub_project'])]
	private ?string $longitude = null;

	#[Groups( [ 'sub_project' ] )]
	private ?string $startDateFormatted = null;

	#[ORM\ManyToOne(targetEntity: City::class)]
	#[ORM\JoinColumn(nullable: true)]
	private City $town;

	public function __construct()
	{
		$this->createdAt = new \DateTime();
	}

	// Getters and Setters
	public function getId(): int {
		return $this->id;
	}

	public function getEntityType(): string {
		return $this->entityType;
	}

	public function setEntityType(string $entityType): self {
		$this->entityType = $entityType;
		return $this;
	}

	public function getEntityId(): int {
		return $this->entityId;
	}

	public function setEntityId(int $entityId): self {
		$this->entityId = $entityId;
		return $this;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name): self {
		$this->name = $name;
		return $this;
	}

	public function getBanner(): string {
		return $this->banner;
	}

	public function setBanner(string $banner): self {
		$this->banner = $banner;
		return $this;
	}

	public function getStartDate(): ?\DateTime {
		return $this->startDate;
	}

	public function setStartDate(\DateTime $startDate): self {
		$this->startDate = $startDate;
		$this->setStartDateFormatted( $startDate->format( 'M Y' ) );
		return $this;
	}

	public function getCity(): ?string {
		return $this->city;
	}

	public function setCity(?string $city): self {
		$this->city = $city;
		return $this;
	}

	public function getIcon(): ?string {
		return $this->icon;
	}

	public function setIcon(?string $icon): self {
		$this->icon = $icon;
		return $this;
	}

	public function getHistoryExcerpt(): string {
		return $this->historyExcerpt;
	}

	public function setHistoryExcerpt(string $historyExcerpt): self {
		$this->historyExcerpt = $historyExcerpt;
		return $this;
	}

	public function getHistoryContent(): string {
		return $this->historyContent;
	}

	public function setHistoryContent(string $historyContent): self {
		$this->historyContent = $historyContent;
		return $this;
	}

	public function getLatitude(): ?string {
		return $this->latitude;
	}

	public function setLatitude(?string $latitude): self {
		$this->latitude = $latitude;
		return $this;
	}

	public function getLongitude(): ?string {
		return $this->longitude;
	}

	public function setLongitude(?string $longitude): self {
		$this->longitude = $longitude;
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

	public function getTown(): City {
		return $this->town;
	}

	public function setTown( City $town ): void {
		$this->town = $town;
	}
}
