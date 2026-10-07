<?php

namespace App\Entity;

use App\Repository\ProjectStepRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProjectStepRepository::class)]
class ProjectStep
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['project_child'])]
    private ?int $id = null;

	#[ORM\ManyToMany(targetEntity: ProjectChild::class, mappedBy: 'projectSteps')]
	private Collection $projectChildren;

    #[ORM\Column(length: 255)]
    #[Groups(['project_child'])]
    private ?string $projectStepName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['project_child'])]
    private ?string $projectStepOrder = null;

	#[ORM\Column(type: 'float', nullable: true)]
	private ?float $percentage = null;

	public function __construct() {
		$this->projectChildren = new ArrayCollection();
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjectStepName(): ?string
    {
        return $this->projectStepName;
    }

    public function setProjectStepName(string $projectStepName): static
    {
        $this->projectStepName = $projectStepName;

        return $this;
    }

    public function getProjectStepOrder(): ?string
    {
        return $this->projectStepOrder;
    }

    public function setProjectStepOrder(string $projectStepOrder): static
    {
        $this->projectStepOrder = $projectStepOrder;

        return $this;
    }

	/**
	 * @return Collection<int, ProjectChild>
	 */
	public function getProjectChildren(): Collection {
		return $this->projectChildren;
	}

	public function addProjectChild(ProjectChild $projectChild): self {
		if (!$this->projectChildren->contains($projectChild)) {
			$this->projectChildren->add($projectChild);
			$projectChild->addProjectStep($this);
		}

		return $this;
	}

	public function removeProjectChild(ProjectChild $projectChild): self {
		if ($this->projectChildren->removeElement($projectChild)) {
			$projectChild->removeProjectStep($this);
		}

		return $this;
	}

	public function getPercentage(): ?float
	{
		return $this->percentage;
	}

	public function setPercentage(?float $percentage): static
	{
		$this->percentage = $percentage;

		return $this;
	}
}
