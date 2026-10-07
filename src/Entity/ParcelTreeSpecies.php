<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity]
class ParcelTreeSpecies
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups( [ 'parcel', 'parcel_tree_species' ] )]
	private ?int $id = null;

	#[ORM\Column]
	#[Groups( [ 'parcel', 'parcel_tree_species' ] )]
	private ?int $treeNumber = null;

	#[ORM\Column]
	#[Groups( [ 'parcel', 'parcel_tree_species' ] )]
	private ?int $availableTreeNumber = null;

	#[ORM\ManyToOne(targetEntity: Parcel::class)]
	#[ORM\JoinColumn(nullable: false)]
	#[Groups( [ 'parcel', 'parcel_tree_species' ] )]
	private ?Parcel $parcel = null;

	#[ORM\ManyToOne(targetEntity: TreeSpecies::class)]
	#[ORM\JoinColumn(nullable: false)]
	#[Groups( [ 'parcel', 'parcel_tree_species' ] )]
	private ?TreeSpecies $treeSpecies = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTreeNumber(): ?int
	{
		return $this->treeNumber;
	}

	public function setTreeNumber(int $treeNumber): self
	{
		$this->treeNumber = $treeNumber;

		return $this;
	}

	public function getAvailableTreeNumber(): ?int
	{
		return $this->availableTreeNumber;
	}

	public function setAvailableTreeNumber(int $availableTreeNumber): self
	{
		$this->availableTreeNumber = $availableTreeNumber;

		return $this;
	}

	public function getParcel(): ?Parcel
	{
		return $this->parcel;
	}

	public function setParcel(?Parcel $parcel): self
	{
		$this->parcel = $parcel;

		return $this;
	}

	public function getTreeSpecies(): ?TreeSpecies
	{
		return $this->treeSpecies;
	}

	public function setTreeSpecies(?TreeSpecies $treeSpecies): self
	{
		$this->treeSpecies = $treeSpecies;

		return $this;
	}
}
