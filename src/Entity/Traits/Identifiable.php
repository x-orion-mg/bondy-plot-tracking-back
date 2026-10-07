<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

trait Identifiable {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	private ?int $id = null;

	#[ORM\Column(type: 'uuid', unique: true)]
	#[Groups( [ 'uuid' ] )]
	private ?Uuid $uuid = null;

	#[ORM\PrePersist]
	public function initializeUuid(): void {
		if ($this->uuid === null) {
			$this->uuid = Uuid::v4();
		}
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getUuid(): ?Uuid {
		return $this->uuid;
	}

	public function setUuid(Uuid $uuid): self {
		$this->uuid = $uuid;
		return $this;
	}
}
