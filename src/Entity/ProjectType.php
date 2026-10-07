<?php

namespace App\Entity;

use App\Entity\Traits\Identifiable;
use App\Entity\Traits\TimeTrackable;
use App\Repository\ProjectTypeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProjectTypeRepository::class)]
#[UniqueEntity('label', message: 'L\'étiquette du statut existe déjà.')]
#[UniqueEntity('slug', message: 'Le Slug existe déjà.')]
class ProjectType
{
	use TimeTrackable;
	use Identifiable;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['project_type'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    #[Groups(['project_type'])]
    private ?string $label = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    #[Groups(['project_type'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::STRING, length: 25, nullable: true)]
    #[Groups(['project_type'])]
    private ?string $color = null;

	public function __construct()
	{
		$this->createdAt = new \DateTime();
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

	public function getUuidAsString(): ?string {
		return $this->uuid?->toRfc4122();
	}
}
