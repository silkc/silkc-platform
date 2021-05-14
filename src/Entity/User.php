<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="user", indexes={@ORM\Index(columns={"firstname", "lastname", "username", "email"}, flags={"fulltext"})})
 */
class User implements UserInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_MODERATOR = 'ROLE_MODERATOR';
    public const ROLE_INSTITUTION = 'ROLE_INSTITUTION';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    protected static $rolesList = [
        self::ROLE_USER         => 'User',
        self::ROLE_MODERATOR    => 'Moderator',
        self::ROLE_INSTITUTION  => 'Institution',
        self::ROLE_ADMIN        => 'Administrator',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $apiToken;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default": "CURRENT_TIMESTAMP"})
     * @var string A "Y-m-d H:i:s" formatted value
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", columnDefinition="DATETIME on update CURRENT_TIMESTAMP")
     * @var string A "Y-m-d H:i:s" formatted value
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     * @var string A "Y-m-d H:i:s" formatted value
     */
    private $tokenCreatedAt;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;
    private $currentPassword;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $homepage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $yearOfBirth;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $completion;

    private $currentOccupations;
    private $previousOccupations;
    private $desiredOccupations;

    private $prePersisted = false;

    /**
     * @ORM\OneToMany(targetEntity=UserOccupation::class, mappedBy="user", orphanRemoval=true)
     */
    private $userOccupations;

    /**
     * @ORM\ManyToMany(targetEntity=Training::class)
     */
    private $trainings;

    /**
     * @ORM\OneToMany(targetEntity=UserSkill::class, mappedBy="user", orphanRemoval=true)
     */
    private $userSkills;

    public function __construct()
    {
        $this->userOccupations = new ArrayCollection();
        $this->currentOccupations = new ArrayCollection();
        $this->previousOccupations = new ArrayCollection();
        $this->desiredOccupations = new ArrayCollection();
        $this->trainings = new ArrayCollection();
        $this->userSkills = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->createdAt = new \DateTime();

        if ($this->prePersisted)
            return;

        $user = $args->getObject();
        $this->_defineCompletion($user);
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        if ($this->prePersisted)
            return;

        $user = $args->getObject();
        $this->_defineCompletion($user);
    }

    protected function _defineCompletion(User $user)
    {
        $userToCompleteProperties = [
            'firstname',
            'lastname',
            'email',
            'username',
            'address',
            'yearOfBirth'
        ];
        $institutionToCompleteProperties = [
            'username',
            'email',
            'address',
            'homepage'
        ];

        $completed = 0;
        $properties = (
            property_exists($user, 'roles') &&
            is_array($user->roles) &&
            in_array(User::ROLE_INSTITUTION, $user->roles)
        ) ?
            $institutionToCompleteProperties :
            $userToCompleteProperties;

        foreach ($properties as $property) {
            if (property_exists($user, $property) && $user->{$property} !== NULL && !empty($user->{$property}))
                $completed++;
        }

        $completion = ($completed === 0) ? 0 : floor(($completed / count($properties)) * 100);
        $user->completion = $completion;
    }

    /**
     * @return array
     */
    public static function getRolesList():array
    {
        return array_flip(static::$rolesList);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getTokenCreatedAt(): ?\DateTimeInterface
    {
        return $this->tokenCreatedAt;
    }
    public function setTokenCreatedAt(\DateTimeInterface $tokenCreatedAt): self
    {
        $this->tokenCreatedAt = $tokenCreatedAt;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getCurrentPassword(): string
    {
        return (string) $this->currentPassword;
    }

    public function setCurrentPassword(string $password): self
    {
        $this->currentPassword = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getHomepage(): ?string
    {
        return $this->homepage;
    }

    public function setHomepage(?string $homepage): self
    {
        $this->homepage = $homepage;

        return $this;
    }

    public function getYearOfBirth(): ?string
    {
        return $this->yearOfBirth;
    }

    public function setYearOfBirth(?string $yearOfBirth): self
    {
        $this->yearOfBirth = $yearOfBirth;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCompletion(): ?string
    {
        return $this->completion;
    }

    public function setCompletion(?string $completion): self
    {
        $this->completion = $completion;

        return $this;
    }

    /**
     * @return Collection|UserOccupation[]
     */
    public function getCurrentOccupations(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isCurrent', 1));

        return $this->userOccupations->matching($criteria);
    }

    /**
     * @return Collection|UserOccupation[]
     */
    public function getDesiredOccupations(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isDesired', 1));

        return $this->userOccupations->matching($criteria);
    }

    /**
     * @return Collection|UserOccupation[]
     */
    public function getPreviousOccupations(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isPrevious', 1));

        return $this->userOccupations->matching($criteria);
    }

    public function addUserOccupation(UserOccupation $userOccupation): self
    {
        if (!$this->userOccupations->contains($userOccupation)) {
            $this->userOccupations[] = $userOccupation;
        }

        return $this;
    }

    public function removeUserOccupation(UserOccupation $userOccupation): self
    {
        $this->userOccupations->removeElement($userOccupation);

        return $this;
    }

    /**
     * @return Collection|UserOccupation[]
     */
    public function getUserOccupations(): Collection
    {
        return $this->userOccupations;
    }

    /**
     * @return Collection|Training[]
     */
    public function getTrainings(): Collection
    {
        return $this->trainings;
    }

    public function addTraining(Training $training): self
    {
        if (!$this->trainings->contains($training)) {
            $this->trainings[] = $training;
        }

        return $this;
    }

    public function removeTraining(Training $training): self
    {
        $this->trainings->removeElement($training);

        return $this;
    }

    /**
     * @return Collection|UserSkill[]
     */
    public function getUserSkills(): Collection
    {
        return $this->userSkills;
    }

    public function addSkill(UserSkill $userSkill): self
    {
        if (!$this->userSkills->contains($userSkill)) {
            $this->userSkills[] = $userSkill;
            $userSkill->setUser($this);
        }

        return $this;
    }

    public function removeUserSkills(UserSkill $userSkill): self
    {
        if ($this->userSkills->removeElement($userSkill)) {
            // set the owning side to null (unless already changed)
            if ($userSkill->getUser() === $this) {
                $userSkill->setUser(null);
            }
        }

        return $this;
    }
}
