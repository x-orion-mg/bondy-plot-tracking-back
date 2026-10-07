<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Country
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	#[Groups(['country'])]
	private ?int $id = null;

	#[ORM\Column(type: Types::STRING, length: 255)]
	#[Groups(['country'])]
	private string $label;

	#[ORM\Column(type: Types::STRING, length: 255)]
	private string $code;

	#[ORM\OneToMany(targetEntity: Region::class, mappedBy: 'country')]
	private Collection $regions;

	public function __construct()
	{
		$this->regions = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function setId(?int $id): void {
		$this->id = $id;
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function setLabel(string $label): void {
		$this->label = $label;
	}

	public function getRegions(): Collection {
		return $this->regions;
	}

	public function setRegions(Collection $regions): void {
		$this->regions = $regions;
	}

	public function getCode(): string {
		return $this->code;
	}

	public function setCode( string $code ): void {
		$this->code = $code;
	}


}
