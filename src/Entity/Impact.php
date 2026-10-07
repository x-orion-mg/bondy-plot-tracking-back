<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Impact
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	private ?int $id = null;

	#[ORM\Column(type: Types::STRING, length: 255)]
	private string $label;

	#[ORM\Column(type: Types::STRING, length: 50)]
	private string $code;

	#[ORM\ManyToOne(targetEntity: ImpactCategory::class, inversedBy: 'impacts')]
	#[ORM\JoinColumn(nullable: false)]
	private ImpactCategory $category;

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

	public function getCode(): string {
		return $this->code;
	}

	public function setCode(string $code): void {
		$this->code = $code;
	}

	public function getCategory(): ImpactCategory {
		return $this->category;
	}

	public function setCategory(ImpactCategory $category): void {
		$this->category = $category;
	}
}
