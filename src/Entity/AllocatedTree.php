<?php

namespace App\Entity;

use App\Entity\Traits\CreatedTimeTrackable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class AllocatedTree
{
	use CreatedTimeTrackable;
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups(['allocated_tree'])]
	private ?int $id = null;

	#[ORM\Column(length: 255)]
	#[Groups(['allocated_tree','allocated_tree_user'])]
	private ?string $trackCode = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(nullable: false)]
	#[Groups(['allocated_tree','allocated_tree_user'])]
	private ?User $user = null;

	#[ORM\ManyToOne(targetEntity: TreeSpecies::class)]
	#[ORM\JoinColumn(nullable: false)]
	#[Groups(['allocated_tree'])]
	private ?TreeSpecies $treeSpecies = null;

	#[ORM\ManyToOne(targetEntity: Parcel::class, inversedBy: 'allocatedTrees')]
	#[ORM\JoinColumn(nullable: false)]
	private ?Parcel $parcel = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTrackCode(): ?string
	{
		return $this->trackCode;
	}

	public function setTrackCode(string $trackCode): self
	{
		$this->trackCode = $trackCode;

		return $this;
	}

	public function getUser(): ?User
	{
		return $this->user;
	}

	public function setUser(?User $user): self
	{
		$this->user = $user;

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

	public function getParcel(): ?Parcel
	{
		return $this->parcel;
	}

	public function setParcel(?Parcel $parcel): self
	{
		$this->parcel = $parcel;

		return $this;
	}
}
