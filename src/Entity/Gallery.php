<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class Gallery
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	#[Groups(['gallery'])]
	private ?int $id = null;

	#[ORM\Column(type: Types::STRING, length: 255)]
	#[Groups(['gallery'])]
	private string $entityType;

	#[ORM\Column(type: Types::INTEGER)]
	#[Groups(['gallery'])]
	private int $entityId;

	#[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
	#[Groups(['gallery'])]
	private ?string $caption = null;

	#[ORM\Column(type: Types::STRING, length: 50)]
	#[Groups(['gallery'])]
	private string $type;

	#[ORM\Column(type: Types::STRING, length: 255)]
	#[Groups(['gallery'])]
	private string $url;

	#[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
	#[Groups(['gallery'])]
	private ?string $thumbnailUrl = null;

	// Getters and Setters

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getEntityType(): string
	{
		return $this->entityType;
	}

	public function setEntityType(string $entityType): self
	{
		$this->entityType = $entityType;
		return $this;
	}

	public function getEntityId(): int
	{
		return $this->entityId;
	}

	public function setEntityId(int $entityId): self
	{
		$this->entityId = $entityId;
		return $this;
	}

	public function getCaption(): ?string
	{
		return $this->caption;
	}

	public function setCaption(?string $caption): self
	{
		$this->caption = $caption;
		return $this;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): self
	{
		$this->type = $type;
		return $this;
	}

	public function getUrl(): string
	{
		return $this->url;
	}

	public function setUrl(string $url): self
	{
		$this->url = $url;
		return $this;
	}

	public function getThumbnailUrl(): ?string
	{
		return $this->thumbnailUrl;
	}

	public function setThumbnailUrl(?string $thumbnailUrl): self
	{
		$this->thumbnailUrl = $thumbnailUrl;
		return $this;
	}
}
