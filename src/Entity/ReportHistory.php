<?php

namespace App\Entity;

use App\Entity\Traits\CreatedTimeTrackable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class ReportHistory
{
	use CreatedTimeTrackable;

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups(['report_history'])]
	private ?int $id = null;

	#[ORM\Column(length: 255)]
	#[Groups(['report_history'])]
	private ?string $fileName = null;

	#[ORM\Column(length: 255)]
	#[Groups(['report_history'])]
	private ?string $filePath = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(nullable: false)]
	private ?User $user = null;

	private ?string $fileSizeMB = null;

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

	public function getFileName(): ?string {
		return $this->fileName;
	}

	public function setFileName( ?string $fileName ): void {
		$this->fileName = $fileName;
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

}
