<?php

namespace App\Entity;

use App\Entity\Traits\Identifiable;
use App\Entity\Traits\TimeTrackable;
use App\Repository\StoryTellingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StoryTellingRepository::class)]
class StoryTelling {
	use TimeTrackable;
	use Identifiable;
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups(['story_telling'])]
	private ?int $id = null;

	#[ORM\Column(type: Types::STRING, length: 255)]
	#[Groups(['story_telling'])]
	private ?string $entityType = null;

	#[ORM\Column(type: Types::INTEGER)]
	#[Groups(['story_telling'])]
	private ?int $entityID = null;

	#[ORM\Column(type: Types::STRING, length: 255)]
	#[Groups(['story_telling'])]
	private ?string $title = null;

	#[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
	#[Groups(['story_telling'])]
	private ?string $bannerImage = null;

	#[ORM\Column(type: Types::TEXT)]
	#[Groups(['story_telling', 'story_telling_tree_species'])]
	private ?string $description = null;

	#[ORM\Column(type: Types::TEXT)]
	#[Groups(['story_telling'])]
	private ?string $content = null;

	public function __construct() {
		$this->createdAt = new \DateTime();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getEntityType(): ?string {
		return $this->entityType;
	}

	public function setEntityType(string $entityType): self {
		$this->entityType = $entityType;
		return $this;
	}

	public function getEntityID(): ?int {
		return $this->entityID;
	}

	public function setEntityID(int $entityID): self {
		$this->entityID = $entityID;
		return $this;
	}

	public function getTitle(): ?string {
		return $this->title;
	}

	public function setTitle(string $title): self {
		$this->title = $title;
		return $this;
	}

	public function getDescription(): ?string {
		return $this->description;
	}

	public function setDescription(string $description): self {
		$this->description = $description;
		return $this;
	}

	public function getContent(): ?string {
		return $this->content;
	}

	public function setContent(string $content): self {
		$this->content = $content;
		return $this;
	}


	public function getBannerImage(): ?string {
		return $this->bannerImage;
	}

	public function setBannerImage(?string $bannerImage): self {
		$this->bannerImage = $bannerImage;
		return $this;
	}

}
