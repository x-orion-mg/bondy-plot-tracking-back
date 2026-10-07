<?php

namespace App\Entity;

use App\Entity\Traits\CreatedTimeTrackable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class CustomerImport
{
	use CreatedTimeTrackable;
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups(['customer_import'])]
	private ?int $id = null;

	#[ORM\Column(length: 255)]
	#[Groups(['customer_import'])]
	private ?string $customerNumbers = null;

	#[ORM\Column(type: 'string', length: 255)]
	#[Groups(['customer_import'])]
	private string $fileName;
	#[ORM\Column(type: 'string', length: 255)]
	#[Groups(['customer_import'])]
	private string $reportFileName;

	#[ORM\ManyToMany(targetEntity: User::class)]
	#[ORM\JoinTable(name: 'customer_import_user')]
	private Collection $users;

	public function __construct()
	{
		$this->users = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getCustomerNumbers(): ?string
	{
		return $this->customerNumbers;
	}

	public function setCustomerNumbers(string $customerNumbers): self
	{
		$this->customerNumbers = $customerNumbers;

		return $this;
	}

	/**
	 * @return Collection<int, User>
	 */
	public function getUsers(): Collection
	{
		return $this->users;
	}

	public function addUser(User $user): self
	{
		if (!$this->users->contains($user)) {
			$this->users->add($user);
		}

		return $this;
	}

	public function removeUser(User $user): self
	{
		$this->users->removeElement($user);

		return $this;
	}

	public function getFileName(): string {
		return $this->fileName;
	}

	public function setFileName( string $fileName ): void {
		$this->fileName = $fileName;
	}

	public function getReportFileName(): string {
		return $this->reportFileName;
	}

	public function setReportFileName( string $reportFileName ): void {
		$this->reportFileName = $reportFileName;
	}


}
