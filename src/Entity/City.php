<?php

namespace App\Entity;

use App\Entity\Traits\CreatedTimeTrackable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class City
{
	use CreatedTimeTrackable;

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups(['city'])]
	private ?int $id = null;

	#[ORM\Column(length: 100, nullable: false)]
	#[Assert\NotBlank(message: 'The label is required')]
	#[Groups(['city'])]
	private ?string $label = null;

	#[ORM\ManyToOne(targetEntity: Region::class, inversedBy: 'cities')]
	#[ORM\JoinColumn(nullable: false)]
	private Region $region;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getLabel(): ?string
	{
		return $this->label;
	}

	public function setLabel(string $label): self
	{
		$this->label = $label;

		return $this;
	}

	public function getRegion(): Region {
		return $this->region;
	}

	public function setRegion( Region $region ): void {
		$this->region = $region;
	}

}
