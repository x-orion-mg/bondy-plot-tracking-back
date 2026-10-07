<?php

namespace App\Entity;

use App\Entity\Traits\CreatedTimeTrackable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Certificate {
	use CreatedTimeTrackable;

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups( [ 'certificate' ] )]
	private ?int $id = null;

	#[ORM\Column( length: 255 )]
	#[Groups( [ 'certificate' ] )]
	private ?string $fileName = null;

	#[ORM\ManyToOne( targetEntity: User::class )]
	#[ORM\JoinColumn( nullable: false )]
	private ?User $user = null;

	#[ORM\Column( length: 255 )]
	#[Groups( [ 'certificate' ] )]
	private ?string $filePath = null;

	private ?string $fileSizeMB = null;
	private ?string $formattedDate = null;

	public function getId(): ?int {
		return $this->id;
	}

	public function getFileName(): ?string {
		return $this->fileName;
	}

	public function setFileName( string $fileName ): self {
		$this->fileName = $fileName;

		return $this;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser( ?User $user ): self {
		$this->user = $user;

		return $this;
	}

	public function getFilePath(): ?string {
		return $this->filePath;
	}

	public function setFilePath( ?string $filePath ): void {
		$this->filePath = $filePath;
	}

	public function getFileSizeMB(): ?string {
		return $this->fileSizeMB;
	}

	public function setFileSizeMB( ?string $fileSizeMB ): void {
		$this->fileSizeMB = $fileSizeMB;
	}

	public function getFormattedDate(): ?string {
		$formatter                = new \IntlDateFormatter(
			'fr_FR',
			\IntlDateFormatter::LONG,
			\IntlDateFormatter::NONE,
			'Europe/Paris',
			\IntlDateFormatter::GREGORIAN,
			'dd MMMM yyyy'
		);
		$this->formattedDate = ucfirst( $formatter->format( $this->getCreatedAt() ) );

		return $this->formattedDate;
	}

	public function setFormattedDate( ?string $formattedDate ): void {
		$this->formattedDate = $formattedDate;
	}

}
