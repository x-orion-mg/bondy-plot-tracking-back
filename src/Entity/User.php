<?php

namespace App\Entity;

use App\Entity\Traits\Identifiable;
use App\Entity\Traits\TimeTrackable;
use App\Entity\Traits\Tokenization;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity( repositoryClass: UserRepository::class )]
#[ORM\UniqueConstraint( name: 'UNIQ_IDENTIFIER_EMAIL', fields: [ 'email' ] )]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
	use TimeTrackable;
	use Identifiable;
	use Tokenization;

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	#[Groups( [ 'user' ] )]
	private ?int $id = null;

	#[ORM\Column( length: 180, nullable: true )]
	#[Groups( [ 'user' ] )]
	private ?string $email = null;

	/**
	 * @var list<string> The user roles
	 */
	#[ORM\Column]
	private array $roles = [];

	/**
	 * @var string The hashed password
	 */
	#[ORM\Column]
	private string $password;

	#[ORM\ManyToOne( targetEntity: Profile::class, inversedBy: 'users' )]
	#[ORM\JoinColumn( nullable: false )]
	private ?Profile $profile = null;

	#[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
	#[Groups( [ 'user' ] )]
	private ?string $firstName = null;

	#[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
	#[Groups( [ 'user' ] )]
	private ?string $lastName = null;

	#[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
	#[Groups( [ 'user' ] )]
	private ?string $logo = null;

	#[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
	private ?string $bannerImage = null;

	#[ORM\Column( type: Types::BOOLEAN, nullable: true )]
	private ?string $enabled = null;

    #[ORM\OneToMany(targetEntity: Certificate::class, mappedBy: 'user', cascade: ['remove'])]
    private Collection $certificates;

    #[ORM\OneToMany(targetEntity: ReportHistory::class, mappedBy: 'user', cascade: ['remove'])]
    private Collection $reportHistories;

	#[ORM\ManyToMany( targetEntity: CustomerImport::class, mappedBy: 'users' )]
	private Collection $customerImports;

	#[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
	#[Groups( [ 'user' ] )]
	private ?string $address = null;

	#[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
	#[Groups( [ 'user' ] )]
	private ?string $phone = null;

	#[ORM\OneToMany( targetEntity: ProjectChild::class, mappedBy: 'user' )]
	private Collection $projectChildren;

	#[ORM\OneToMany( targetEntity: UserOption::class, mappedBy: 'user', cascade: [ 'persist', 'remove' ] )]
	private Collection $options;

	public function __construct() {
		$this->certificates    = new ArrayCollection();
		$this->reportHistories = new ArrayCollection();
		$this->customerImports = new ArrayCollection();
		$this->projectChildren = new ArrayCollection();
		$this->options         = new ArrayCollection();

	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getEmail(): ?string {
		return $this->email;
	}

	public function setEmail( string $email ): static {
		$this->email = $email;

		return $this;
	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUserIdentifier(): string {
		return (string) $this->email;
	}

	/**
	 * @return list<string>
	 * @see UserInterface
	 *
	 */
	public function getRoles(): array {
		$roles   = $this->roles;
		$roles[] = 'ROLE_USER';

		return array_unique( $roles );
	}

	/**
	 * @param list<string> $roles
	 */
	public function setRoles( array $roles ): static {
		$this->roles = $roles;

		return $this;
	}

	/**
	 * @see PasswordAuthenticatedUserInterface
	 */
	public function getPassword(): ?string {
		return $this->password;
	}

	public function setPassword( string $password ): static {
		$this->password = $password;

		return $this;
	}

	public function getProfile(): ?Profile {
		return $this->profile;
	}

	public function setProfile( ?Profile $profile ): static {
		$this->profile = $profile;

		return $this;
	}

	public function eraseCredentials(): void {
		// Clear sensitive data here if necessary
	}

	public function getFirstName(): ?string {
		return $this->firstName;
	}

	public function setFirstName( ?string $firstName ): void {
		$this->firstName = $firstName;
	}

	public function getLastName(): ?string {
		return $this->lastName;
	}

	public function setLastName( ?string $lastName ): void {
		$this->lastName = $lastName;
	}

	public function getLogo(): ?string {
		return $this->logo;
	}

	public function setLogo( ?string $logo ): void {
		$this->logo = $logo;
	}

	public function getBannerImage(): ?string {
		return $this->bannerImage;
	}

	public function setBannerImage( ?string $bannerImage ): void {
		$this->bannerImage = $bannerImage;
	}

	public function getEnabled(): ?string {
		return $this->enabled;
	}

	public function setEnabled( ?string $enabled ): void {
		$this->enabled = $enabled;
	}

	/**
	 * @return Collection<int, Certificate>
	 */
	public function getCertificates(): Collection {
		return $this->certificates;
	}

	public function addCertificate( Certificate $certificate ): self {
		if ( ! $this->certificates->contains( $certificate ) ) {
			$this->certificates->add( $certificate );
			$certificate->setUser( $this );
		}

		return $this;
	}

	public function removeCertificate( Certificate $certificate ): self {
		if ( $this->certificates->removeElement( $certificate ) && $certificate->getUser() === $this ) {
			$certificate->setUser( null );
		}

		return $this;
	}

	/**
	 * @return Collection<int, ReportHistory>
	 */
	public function getReportHistories(): Collection {
		return $this->reportHistories;
	}

	public function addReportHistory( ReportHistory $reportHistory ): self {
		if ( ! $this->reportHistories->contains( $reportHistory ) ) {
			$this->reportHistories->add( $reportHistory );
			$reportHistory->setUser( $this );
		}

		return $this;
	}

	public function removeReportHistory( ReportHistory $reportHistory ): self {
		if ( $this->reportHistories->removeElement( $reportHistory ) && $reportHistory->getUser() === $this ) {
			$reportHistory->setUser( null );
		}

		return $this;
	}

	/**
	 * @return Collection<int, CustomerImport>
	 */
	public function getCustomerImports(): Collection {
		return $this->customerImports;
	}

	public function addCustomerImport( CustomerImport $customerImport ): self {
		if ( ! $this->customerImports->contains( $customerImport ) ) {
			$this->customerImports->add( $customerImport );
			$customerImport->addUser( $this );
		}

		return $this;
	}

	public function removeCustomerImport( CustomerImport $customerImport ): self {
		if ( $this->customerImports->removeElement( $customerImport ) ) {
			$customerImport->removeUser( $this );
		}

		return $this;
	}

	public function getAddress(): ?string {
		return $this->address;
	}

	public function setAddress( ?string $address ): void {
		$this->address = $address;
	}

	public function getPhone(): ?string {
		return $this->phone;
	}

	public function setPhone( ?string $phone ): void {
		$this->phone = $phone;
	}

	/**
	 * @return Collection<int, ProjectChild>
	 */
	public function getProjectChildren(): Collection {
		return $this->projectChildren;
	}

	public function addProjectChild( ProjectChild $projectChild ): self {
		if ( ! $this->projectChildren->contains( $projectChild ) ) {
			$this->projectChildren->add( $projectChild );
			$projectChild->setUser( $this );
		}

		return $this;
	}

	public function removeProjectChild( ProjectChild $projectChild ): self {
		if ( $this->projectChildren->removeElement( $projectChild ) && $projectChild->getUser() === $this ) {
			$projectChild->setUser( null );
		}

		return $this;
	}

	public function getOptions(): Collection {
		return $this->options;
	}

	public function addOption( UserOption $option ): self {
		if ( ! $this->options->contains( $option ) ) {
			$this->options[] = $option;
			$option->setUser( $this );
		}

		return $this;
	}

	public function removeOption( UserOption $option ): self {
		if ( $this->options->removeElement( $option ) && $option->getUser() === $this ) {
			$option->setUser( null );
		}

		return $this;
	}

	/**
	 * Permet de récupérer une option spécifique
	 */
	public function getOptionValue( string $name ): mixed {
		foreach ( $this->options as $option ) {
			if ( $option->getName() === $name ) {
				return $option->getValue();
			}
		}

		return null;
	}

	/**
	 * Permet de définir une option spécifique
	 */
	public function setOptionValue( string $name, mixed $value ): self {
		foreach ( $this->options as $option ) {
			if ( $option->getName() === $name ) {
				$option->setValue( $value );

				return $this;
			}
		}

		// Si l'option n'existe pas, on la crée
		$option = new UserOption();
		$option->setName( $name )->setValue( $value );
		$this->addOption( $option );

		return $this;
	}

}
