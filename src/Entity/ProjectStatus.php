<?php

namespace App\Entity;

use App\Repository\ProjectStatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProjectStatusRepository::class)]
#[UniqueEntity('label', message: 'L\'étiquette du statut existe déjà.')]
#[UniqueEntity('slug', message: 'Le Slug existe déjà.')]
class ProjectStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['project_status'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    #[Groups(['project_status'])]
    private ?string $label = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    #[Groups(['project_status'])]
    private ?string $slug = null;

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
}
