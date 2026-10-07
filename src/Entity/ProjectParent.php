<?php

namespace App\Entity;

use App\Entity\Traits\Identifiable;
use App\Entity\Traits\TimeTrackable;
use App\Repository\ProjectParentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity( repositoryClass: ProjectParentRepository::class )]
class ProjectParent {
	use TimeTrackable;
	use Identifiable;
	private const SET_NULL = 'SET NULL';

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups(['project_child', 'parcel', 'project_parent'])]
	private ?int $id = null;

	#[ORM\Column(type: Types::STRING, length: 50)]
	#[Assert\NotBlank(message: "Le nom du projet est obligatoire")]
	#[Assert\Length(
		min: 3,
		max: 50,
		minMessage: "Le nom du projet doit contenir au moins {{ limit }} caractères.",
		maxMessage: "Le nom du projet ne peut pas dépasser {{ limit }} caractères."
	)]
	#[Assert\Regex(
		pattern: '/^[a-zA-Z].*/',
		message: "Le nom du projet doit commencer par une lettre."
	)]
	#[Groups(['project_parent'])]
	private ?string $title = null;

	#[ORM\Column( type: Types::TEXT, length: 30, nullable: true )]
	#[Groups(['project_parent'])]
	private ?string $city = null;

	#[ORM\Column( type: Types::DATETIME_MUTABLE )]
	private ?\DateTime $startDate = null;

	#[ORM\Column( type: Types::TEXT, nullable: true )]
	#[Groups(['project_parent'])]
	private ?string $history = null;

	#[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
	#[Groups(['project_parent'])]
	private ?string $bannerImage = null;

	#[ORM\ManyToOne( targetEntity: ProjectType::class )]
	#[ORM\JoinColumn( nullable: true, onDelete: self::SET_NULL )]
	#[Groups(['project_parent'])]
	private ?ProjectType $projectType = null;

	#[ORM\ManyToOne( targetEntity: ProjectStatus::class )]
	#[ORM\JoinColumn( nullable: true, onDelete: self::SET_NULL )]
	#[Groups(['project_parent'])]
	private ?ProjectStatus $projectStatus = null;

	#[ORM\ManyToOne( targetEntity: Localisation::class )]
	#[ORM\JoinColumn( nullable: true, onDelete: self::SET_NULL )]
	#[Groups(['project_child', 'parcel','parcel_list', 'project_parent'])]

	private ?Localisation $localisation = null;

	#[ORM\ManyToOne(targetEntity: Region::class)]
	#[ORM\JoinColumn(nullable: false)]
	#[Groups(['project_parent'])]
	private Region $region;

	#[ORM\ManyToOne(targetEntity: City::class)]
	#[ORM\JoinColumn(nullable: true)]
	#[Groups(['project_parent'])]
	private City $town;
	#[Groups(['project_parent'])]
	private $galleries;
	#[Groups(['project_parent'])]
	private $subProjects;
	#[Groups(['project_parent'])]
	private $startDateFormatted ;

    #[ORM\OneToMany(mappedBy: 'projectParent', targetEntity: ProjectChild::class, cascade: ['persist', 'remove'])]
    #[Groups(['project_parent'])]
    private Collection $projectChildren;

	public function __construct() {
		$this->createdAt = new \DateTime();
        $this->projectChildren = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function setId(int $id): self {
		$this->id = $id;
		return $this;
	}

	public function getCity(): ?string {
		return $this->city;
	}

	public function setCity( ?string $city ): static {
		$this->city = $city;

		return $this;
	}

	public function getStartDate(): ?\DateTime {
		return $this->startDate;
	}

	public function setStartDate( \DateTime $startDate ): static {
		$this->startDate = $startDate;
		$this->setStartDateFormatted( $startDate->format( 'M Y' ) );

		return $this;
	}

	public function getHistory(): ?string {
		return $this->history;
	}

	public function setHistory( ?string $history ): static {
		$this->history = $history;

		return $this;
	}

	public function getBannerImage(): ?string {
		return $this->bannerImage;
	}

	public function setBannerImage( ?string $bannerImage ): static {
		$this->bannerImage = $bannerImage;

		return $this;
	}

	public function getProjectType(): ?ProjectType {
		return $this->projectType;
	}

	public function setProjectType( ?ProjectType $projectType ): static {
		$this->projectType = $projectType;

		return $this;
	}

	public function getProjectStatus(): ?ProjectStatus {
		return $this->projectStatus;
	}

	public function setProjectStatus( ?ProjectStatus $projectStatus ): static {
		$this->projectStatus = $projectStatus;

		return $this;
	}

	public function getLocalisation(): ?Localisation {
		return $this->localisation;
	}

	public function setLocalisation( ?Localisation $localisation ): static {
		$this->localisation = $localisation;

		return $this;
	}

	public function setGalleries( $galleries ): self {
		$this->galleries = $galleries;

		return $this;
	}

	public function getGalleries() {
		return $this->galleries;
	}

	public function setSubProjects( $subProjects ): self {
		$this->subProjects = $subProjects;

		return $this;
	}

	public function getSubProjects() {
		return $this->subProjects;
	}

	public function setStartDateFormatted( ?string $startDateFormatted ): self {
		$this->startDateFormatted = $startDateFormatted;

		return $this;
	}

	public function getStartDateFormatted(): ?string {
		$formatter = new \IntlDateFormatter(
			'fr_FR',
			\IntlDateFormatter::LONG,
			\IntlDateFormatter::NONE,
			'Europe/Paris',
			\IntlDateFormatter::GREGORIAN,
			'MMMM yyyy'
		);
		$this->startDateFormatted = ucfirst($formatter->format($this->startDate));

		return $this->startDateFormatted;
	}

	public function getTitle(): ?string {
		return $this->title;
	}

	public function setTitle( ?string $title ): static {
		$this->title = $title;

		return $this;
	}

	public function getRegion(): Region {
		return $this->region;
	}

	public function setRegion( Region $region ): void {
		$this->region = $region;
	}

	public function getTown(): City {
		return $this->town;
	}

	public function setTown( City $town ): void {
		$this->town = $town;
	}

    // Getter for projectChildren
    public function getProjectChildren(): Collection {
        return $this->projectChildren;
    }

    // Add a ProjectChild
    public function addProjectChild(ProjectChild $projectChild): self {
        if (!$this->projectChildren->contains($projectChild)) {
            $this->projectChildren[] = $projectChild;
            $projectChild->setProjectParent($this);
        }

        return $this;
    }

    // Remove a ProjectChild
    public function removeProjectChild(ProjectChild $projectChild): self {
        if ($this->projectChildren->removeElement($projectChild)) {
            if ($projectChild->getProjectParent() === $this) {
                $projectChild->setProjectParent(null);
            }
        }

        return $this;
    }
}
