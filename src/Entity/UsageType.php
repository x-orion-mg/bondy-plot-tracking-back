<?php

namespace App\Entity;

use App\Entity\Traits\Identifiable;
use App\Entity\Traits\TimeTrackable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class UsageType
{
	use TimeTrackable;
	use Identifiable;
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups( [ 'usage_type' ] )]
	private ?int $id = null;

	#[ORM\Column(type: Types::STRING, length: 255)]
	#[Groups( [ 'usage_type' ] )]
	private ?string $label = null;

	#[ORM\Column(type: Types::STRING ,length: 255, nullable: true)]
	#[Groups( [ 'usage_type' ] )]
	private ?string $icon = null;

	public function __construct() {
		$this->createdAt = new \DateTime();
	}

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

	public function getIcon(): ?string
	{
		return $this->icon;
	}

	public function setIcon(?string $icon): self
	{
		$this->icon = $icon;

		return $this;
	}
}
