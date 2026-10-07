<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait Tokenization {
	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['token'])]
	private ?string $token = null;

	/**
	 * @throws \Exception
	 */
	#[ORM\PrePersist]
	public function initializeToken(): void {
		if ($this->token === null) {
			$this->token = bin2hex(random_bytes(16));
		}
	}

	public function getToken(): ?string {
		return $this->token;
	}

	public function setToken(string $token): self {
		$this->token = $token;
		return $this;
	}
}
