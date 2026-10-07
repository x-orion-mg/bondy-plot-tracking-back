<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class ImpactCategory
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	private ?int $id = null;

	#[ORM\Column(type: Types::STRING, length: 255)]
	private string $name;

	#[ORM\OneToMany(targetEntity: Impact::class, mappedBy: 'category')]
	private Collection $impacts;

	#[ORM\Column(type: Types::INTEGER)]
	private string $enum;

	public function __construct()
	{
		$this->impacts = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function setId(?int $id): void {
		$this->id = $id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name): void {
		$this->name = $name;
	}

	public function getImpacts(): Collection {
		return $this->impacts;
	}

	public function setImpacts(Collection $impacts): void {
		$this->impacts = $impacts;
	}

	public function getEnum(): string {
		return $this->enum;
	}

	public function setEnum(string $enum): void {
		$this->enum = $enum;
	}
}
