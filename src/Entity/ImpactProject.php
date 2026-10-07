<?php

namespace App\Entity;

use App\Entity\Traits\TimeTrackable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ImpactProject
{
	use TimeTrackable;
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	private ?int $id = null;

	#[ORM\Column(type: Types::FLOAT)]
	private float $value;

	#[ORM\ManyToOne(targetEntity: ProjectChild::class, inversedBy: 'impactProjects')]
	#[ORM\JoinColumn(nullable: false)]
	private ProjectChild $projectChild;

	#[ORM\ManyToOne(targetEntity: Impact::class)]
	#[ORM\JoinColumn(nullable: false)]
	private Impact $impact;


	public function getId(): ?int {
		return $this->id;
	}

	public function setId( ?int $id ): void {
		$this->id = $id;
	}

	public function getValue(): float {
		return $this->value;
	}

	public function setValue( float $value ): void {
		$this->value = $value;
	}

	public function getProjectChild(): ProjectChild {
		return $this->projectChild;
	}

	public function setProjectChild( ProjectChild $projectChild ): void {
		$this->projectChild = $projectChild;
	}

	public function getImpact(): Impact {
		return $this->impact;
	}

	public function setImpact( Impact $impact ): void {
		$this->impact = $impact;
	}
}
