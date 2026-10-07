<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserOptionRepository;

#[ORM\Entity(repositoryClass: UserOptionRepository::class)]
#[ORM\Table(name: 'user_option')]
#[ORM\UniqueConstraint(name: 'user_option_unique', columns: ['user_id', 'name'])]
class UserOption
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: Types::INTEGER)]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'options')]
	#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
	private ?User $user = null;

	#[ORM\Column(type:Types::STRING, length: 50)]
	private string $name;

	#[ORM\Column(type: Types::JSON)]
	private mixed $value = null;

	public function getId(): ?int
	{
		return $this->id;
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

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Récupérer la valeur en décodant JSON
	 */
	public function getValue(): mixed
	{
		// Si c'est null, retourner null directement
		if ($this->value === null) {
			return null;
		}

		// Si c'est déjà un type simple (bool, string, int, float), le retourner tel quel
		if (is_bool($this->value) || is_string($this->value) || is_int($this->value) || is_float($this->value)) {
			return $this->value;
		}

		// Sinon, essayer de décoder JSON
		return json_decode($this->value, true);
	}

	/**
	 * Définir une valeur en l'encodant en JSON si nécessaire
	 */
	public function setValue(mixed $value): self
	{
		// Si c'est null, on le stocke tel quel
		if ($value === null) {
			$this->value = null;
		}
		// Si c'est un type simple (bool, string, int, float), pas besoin d'encoder
		elseif (is_bool($value) || is_string($value) || is_int($value) || is_float($value)) {
			$this->value = $value;
		}
		// Si c'est un tableau ou un objet, on encode en JSON
		else {
			$this->value = json_encode($value);
		}

		return $this;
	}
}
