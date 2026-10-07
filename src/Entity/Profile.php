<?php

namespace App\Entity;

use App\Repository\ProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfileRepository::class)]
class Profile
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column(length: 255)]
	private ?string $label = null;

	#[ORM\Column(length: 50, unique: true)]
	private ?string $code = null;

	#[ORM\OneToMany(mappedBy: 'profile', targetEntity: User::class)]
	private Collection $users;

	#[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'profiles')]
	private Collection $roles;

	public function __construct()
	{
		$this->users = new ArrayCollection();
		$this->roles = new ArrayCollection();
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
	 * @return Collection<int, User>
	 */
	public function getUsers(): Collection
	{
		return $this->users;
	}

	public function addUser(User $user): static
	{
		if (!$this->users->contains($user)) {
			$this->users->add($user);
			$user->setProfile($this);
		}

		return $this;
	}

	public function removeUser(User $user): static
	{
		if ($this->users->removeElement($user)) {
			if ($user->getProfile() === $this) {
				$user->setProfile(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, Role>
	 */
	public function getRoles(): Collection
	{
		return $this->roles;
	}

	public function addRole(Role $role): static
	{
		if (!$this->roles->contains($role)) {
			$this->roles->add($role);
		}

		return $this;
	}

	public function removeRole(Role $role): static
	{
		$this->roles->removeElement($role);

		return $this;
	}
}
