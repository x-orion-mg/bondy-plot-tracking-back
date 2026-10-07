<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Region
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	#[Groups(['region'])]
	private ?int $id = null;

	#[ORM\Column(type: Types::STRING, length: 255)]
	#[Groups(['region'])]
	private string $label;

	#[ORM\Column(length: 50, nullable: false)]
	#[Assert\NotBlank(message: 'Le Shape ID est obligatoire')]
	#[Groups(['region'])]
	private ?string $shapeId = null;

	#[ORM\Column(type: 'json', nullable: false)]
	#[Assert\NotBlank(message: 'Ces coordonnées sont obligatoires')]
	#[Groups(['region'])]
	private array $bounds = [];

	#[ORM\Column(type: Types::FLOAT)]
	#[Groups(['region'])]
	private float $longitude;

	#[ORM\Column(type: Types::FLOAT)]
	#[Groups(['region'])]
	private float $latitude;

	#[ORM\OneToMany( targetEntity: City::class, mappedBy: 'region' )]
	private Collection $cities;

	#[ORM\ManyToOne(targetEntity: Country::class, inversedBy: 'regions')]
	#[ORM\JoinColumn(nullable: false)]
	#[Groups(['region'])]
	private Country $country;

	public function __construct()
	{
		$this->cities = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function setId( ?int $id ): void {
		$this->id = $id;
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function setLabel( string $label ): void {
		$this->label = $label;
	}

	public function getLongitude(): float {
		return $this->longitude;
	}

	public function setLongitude( float $longitude ): void {
		$this->longitude = $longitude;
	}

	public function getLatitude(): float {
		return $this->latitude;
	}

	public function setLatitude( float $latitude ): void {
		$this->latitude = $latitude;
	}

	public function getCities(): Collection {
		return $this->cities;
	}

	public function setCities( Collection $cities ): void {
		$this->cities = $cities;
	}

	public function getCountry(): Country {
		return $this->country;
	}

	public function setCountry( Country $country ): void {
		$this->country = $country;
	}

	public function getShapeId(): ?string {
		return $this->shapeId;
	}

	public function setShapeId( ?string $shapeId ): void {
		$this->shapeId = $shapeId;
	}

	public function getBounds(): array {
		return $this->bounds;
	}

	public function setBounds( array $bounds ): void {
		$this->bounds = $bounds;
	}

}
