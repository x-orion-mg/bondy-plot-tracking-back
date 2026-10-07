<?php

namespace App\Entity;

use App\Repository\SIEMEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
#[ORM\Entity(repositoryClass: SIEMEntryRepository::class)]
class SIEMEntry
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(type: 'datetime_immutable')]
	private \DateTimeInterface $eventTime;

	#[ORM\Column(type: Types::STRING ,length: 255)]
	private ?string $sourceAddr = null;

	#[ORM\Column]
	private ?int $eventResult = null;

	#[ORM\Column(type: Types::STRING, nullable: true)]
	private ?string $additionalInfo = null;

	#[ORM\Column(type: Types::INTEGER, nullable: true)]
	private ?int $targetSourceId = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	private ?User $user = null;

	#[ORM\ManyToOne(targetEntity: ErrorType::class)]
	private ?ErrorType $errorType = null;

	#[ORM\ManyToOne(targetEntity: EventType::class)]
	private ?EventType $eventType = null;

	#[ORM\ManyToOne(targetEntity: ResourceType::class)]
	private ?ResourceType $resourceType = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getEventTime(): \DateTimeInterface
	{
		return $this->eventTime;
	}

	public function setEventTime(\DateTimeInterface $eventTime): self
	{
		$this->eventTime = $eventTime;

		return $this;
	}

	public function getSourceAddr(): ?string
	{
		return $this->sourceAddr;
	}

	public function setSourceAddr(string $sourceAddr): self
	{
		$this->sourceAddr = $sourceAddr;

		return $this;
	}

	public function getEventResult(): ?int
	{
		return $this->eventResult;
	}

	public function setEventResult(int $eventResult): self
	{
		$this->eventResult = $eventResult;

		return $this;
	}

	public function getAdditionalInfo(): ?string
	{
		return $this->additionalInfo;
	}

	public function setAdditionalInfo(?string $additionalInfo): self
	{
		$this->additionalInfo = $additionalInfo;

		return $this;
	}

	public function getTargetSourceId(): ?int
	{
		return $this->targetSourceId;
	}

	public function setTargetSourceId(int $targetSourceId): self
	{
		$this->targetSourceId = $targetSourceId;

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

	public function getErrorType(): ?ErrorType
	{
		return $this->errorType;
	}

	public function setErrorType(?ErrorType $errorType): self
	{
		$this->errorType = $errorType;

		return $this;
	}

	public function getEventType(): ?EventType
	{
		return $this->eventType;
	}

	public function setEventType(?EventType $eventType): self
	{
		$this->eventType = $eventType;

		return $this;
	}

	public function getResourceType(): ?ResourceType
	{
		return $this->resourceType;
	}

	public function setResourceType(?ResourceType $resourceType): self
	{
		$this->resourceType = $resourceType;

		return $this;
	}
}
