<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Security\Core\User\UserInterface;

trait CreatedTimeTrackable {
	#[ORM\Column( type: Types::DATETIME_MUTABLE, nullable: false )]
	private ?\DateTime $createdAt;

	#[ORM\ManyToOne( targetEntity: 'App\Entity\User' )]
	#[ORM\JoinColumn( nullable: true )]
	private ?UserInterface $createdBy = null;

	public function __construct() {
		$this->createdAt = new \DateTime( 'now' );
	}

	public function getCreatedAt(): ?\DateTime {
		return $this->createdAt;
	}

	public function setCreatedAt( \DateTime $createdAt ): self {
		$this->createdAt = $createdAt;

		return $this;
	}

	public function getCreatedBy(): ?string {
		return $this->createdBy;
	}

	public function setCreatedBy( ?UserInterface $createdBy ): self {
		$this->createdBy = $createdBy;

		return $this;
	}
}
