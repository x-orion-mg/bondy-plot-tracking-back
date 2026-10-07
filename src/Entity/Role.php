<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(type:Types::STRING ,length: 255)]
	private ?string $label = null;

	#[ORM\Column( type:Types::STRING ,length: 50, unique: true)]
	private ?string $code = null;

	#[ORM\ManyToMany(targetEntity: Profile::class, mappedBy: 'roles')]
	private Collection $profiles;

	public function __construct()
	{
		$this->profiles = new ArrayCollection();
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

	public function getCode(): ?string
	{
		return $this->code;
	}

	public function setCode(string $code): static
	{
		$this->code = $code;

		return $this;
	}

	/**
	 * @return Collection<int, Profile>
	 */
	public function getProfiles(): Collection
	{
		return $this->profiles;
	}

	public function addProfile(Profile $profile): static
	{
		if (!$this->profiles->contains($profile)) {
			$this->profiles->add($profile);
			$profile->addRole($this);
		}

		return $this;
	}

	public function removeProfile(Profile $profile): static
	{
		if ($this->profiles->removeElement($profile)) {
			$profile->removeRole($this);
		}

		return $this;
	}
}
