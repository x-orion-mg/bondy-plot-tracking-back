<?php

namespace App\Entity;

use App\Repository\LocalisationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LocalisationRepository::class)]
class Localisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25, nullable: false)]
    #[Assert\NotBlank(message: 'Le Pays est obligatoire')]
    #[Groups(['project_child','localisation','sub_project', 'parcel','parcel_list'])]
    private ?string $country = null;

    #[ORM\Column(length: 50, nullable: false)]
    #[Assert\NotBlank(message: 'La Région est obligatoire')]
    #[Groups(['project_child','localisation','sub_project', 'parcel','parcel_list'])]
    private ?string $region = null;

    #[ORM\Column(length: 50, nullable: false)]
    #[Assert\NotBlank(message: 'Le Shape ID est obligatoire')]
    #[Groups(['project_child','localisation', 'parcel'])]
    private ?string $shapeId = null;

    #[ORM\Column(type: 'json', nullable: false)]
    #[Assert\NotBlank(message: 'Ces coordonnées sont obligatoires')]
    #[Groups(['project_child','localisation'])]
    private array $bounds = [];

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    #[Groups(['project_child','localisation'])]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    #[Groups(['project_child','localisation'])]
    private ?string $longitude = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getShapeId(): ?string
    {
        return $this->shapeId;
    }

    public function setShapeId(string $shapeId): static
    {
        $this->shapeId = $shapeId;

        return $this;
    }

    public function getBounds(): array
    {
        return $this->bounds;
    }

    public function setBounds(array $bounds): self
    {
        $this->bounds = $bounds;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }
}
