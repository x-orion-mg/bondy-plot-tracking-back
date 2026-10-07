<?php

namespace App\Entity;

use App\Entity\Traits\Identifiable;
use App\Entity\Traits\TimeTrackable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class TreeSpecies {

	use Identifiable;
	use TimeTrackable;
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups( [ 'tree_species', 'parcel','tree_species_show','tree_species_dashboard' ] )]
	private ?int $id = null;

	#[ORM\Column( type: Types::STRING, length: 255 )]
	#[Groups( [ 'tree_species','tree_species_dashboard'] )]
	private ?string $commonName = null;

	#[ORM\Column( type: Types::STRING, length: 255 )]
	#[Groups( [ 'tree_species','tree_species_dashboard' ] )]
	private ?string $scientificName = null;

	#[ORM\Column( type: Types::STRING, length: 255 )]
	#[Groups( [ 'tree_species' ] )]
	private ?string $trackCode = null;

	#[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
	#[Groups( [ 'tree_species' ] )]
	private ?string $banner = null;

	#[ORM\Column(type: Types::STRING , length: 255, nullable: true )]
	#[Groups( [ 'tree_species' ] )]
	private ?string $icon = null;

	#[ORM\ManyToOne( targetEntity: UsageType::class )]
	#[ORM\JoinColumn( nullable: false )]
	#[Groups( [ 'tree_species'] )]
	private ?UsageType $usageType = null;

	#[ORM\Column(type: Types::STRING, length: 7, nullable: true)]
	#[Groups(['tree_species','tree_species_dashboard'])]
	private ?string $color = null;

	public function __construct() {
		$this->createdAt = new \DateTime();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getCommonName(): ?string {
		return $this->commonName;
	}

	public function setCommonName( string $commonName ): self {
		$this->commonName = $commonName;

		return $this;
	}

	public function getScientificName(): ?string {
		return $this->scientificName;
	}

	public function setScientificName( string $scientificName ): self {
		$this->scientificName = $scientificName;

		return $this;
	}

	public function getTrackCode(): ?string {
		return $this->trackCode;
	}

	public function setTrackCode( string $trackCode ): self {
		$this->trackCode = $trackCode;

		return $this;
	}

	public function getBanner(): ?string {
		return $this->banner;
	}

	public function setBanner( ?string $banner ): self {
		$this->banner = $banner;

		return $this;
	}

	public function getIcon(): ?string {
		return $this->icon;
	}

	public function setIcon( ?string $icon ): self {
		$this->icon = $icon;

		return $this;
	}

	public function getUsageType(): ?UsageType {
		return $this->usageType;
	}

	public function setUsageType( ?UsageType $usageType ): self {
		$this->usageType = $usageType;

		return $this;
	}

	public function getColor(): ?string {
		return $this->color;
	}

	public function setColor(?string $color): self {
		$this->color = $color;
		return $this;
	}
}
