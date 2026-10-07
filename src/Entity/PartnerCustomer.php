<?php

namespace App\Entity;

use App\Entity\Traits\CreatedTimeTrackable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PartnerCustomer
{
	use CreatedTimeTrackable;
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', nullable: false)]
	private ?User $user = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(name: 'partner_id', referencedColumnName: 'id', nullable: false)]
	private ?User $partner = null;

	public function getId(): ?int
	{
		return $this->id;
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

	public function getPartner(): ?User
	{
		return $this->partner;
	}

	public function setPartner(?User $partner): self
	{
		$this->partner = $partner;

		return $this;
	}
}
