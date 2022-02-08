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
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="user", indexes={@ORM\Index(columns={"firstname", "lastname", "username", "email"}, flags={"fulltext"})})
 * @UniqueEntity(
 *     fields={"username"},
 *     message="This username already exists."
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     message="This email already exists."
 * )
 * @ApiResource(
 *      normalizationContext={"groups"={"user:read"}}
 * )
 */
class User implements UserInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_MODERATOR = 'ROLE_MODERATOR';
    public const ROLE_INSTITUTION = 'ROLE_INSTITUTION';
    public const ROLE_RECRUITER = 'ROLE_RECRUITER';
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
     * @Groups({"user:read", "training:read", "training_feedback:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "lastname cannot be longer than {{ limit }} characters"
     * )
     * @Groups({"user:read", "training:read", "training_feedback:read"})
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "firstname cannot be longer than {{ limit }} characters"
     * )
     * @Groups({"user:read", "training:read", "training_feedback:read"})
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=100, nullable=true, unique=true)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "username cannot be longer than {{ limit }} characters"
     * )
     * @Groups({"user:read", "training:read", "training_feedback:read"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "Your email address must be at least {{ limit }} characters long",
     *      maxMessage = "Your email address cannot be longer than {{ limit }} characters"
     * )
     * @Groups({"user:read", "training:read", "training_feedback:read"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     */
    private $apiToken;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default": "CURRENT_TIMESTAMP"})
     * @Assert\Type("datetime")
     * @var string A "Y-m-d H:i:s" formatted value
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", columnDefinition="DATETIME on update CURRENT_TIMESTAMP")
     * @Assert\Type("datetime")
     * @var string A "Y-m-d H:i:s" formatted value
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Type("datetime")
     * @var string A "Y-m-d H:i:s" formatted value
     */
    private $tokenCreatedAt;

    /**
     * @ORM\Column(type="array")
     * @Groups({"user:read"})
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
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "homepage cannot be longer than {{ limit }} characters"
     * )
     */
    private $homepage;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var string A "Y-m-d H:i:s" formatted value
     */
    private $dateOfBirth;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "address cannot be longer than {{ limit }} characters"
     * )
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "user longitude cannot be longer than {{ limit }} characters"
     * )
     * @Groups({"user:read"})
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "user latitude cannot be longer than {{ limit }} characters"
     * )
     * @Groups({"user:read"})
     */
    private $latitude;

    /**
     * @ORM\Column(type="integer", length=3, nullable=false, options={"default": 0, "unsigned": true})
     * @Assert\Type("integer")
     * @Assert\Length(
     *      max = 3,
     *      maxMessage = "completion cannot be longer than {{ limit }} characters"
     * )
     */
    private $completion = 0;

    /**
     * @ORM\Column(type="boolean", options={"unsigned": true, "default": 0})
     * @Groups({"user:read"})
     */
    private $isValidated = 0;

    /**
     * @ORM\Column(type="boolean", options={"unsigned": true, "default": 0})
     * @Groups({"user:read"})
     */
    private $isSuspended = 0;

    /**
     * @ORM\Column(type="boolean", options={"unsigned": true, "default": 0})
     * @Groups({"user:read"})
     */
    private $isSuspected = 0;

    /**
     * @ORM\Column(type="boolean", options={"unsigned": true, "default": 1})
     * @Groups({"user:read"})
     */
    private $isSearchesKept = 1;

    /**
     * @ORM\Column(type="boolean", options={"unsigned": true, "default": 1})
     */
    private $isListeningPosition = 0;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned": true})
     */
    private $upToDistance;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned": true})
     */
    private $professionalExperience;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"unsigned": true})
     */
    private $code;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type("datetime")
     * @var string A "Y-m-d H:i:s" formatted value
     */
    private $codeCreatedAt;

    private $currentOccupations;
    private $previousOccupations;
    private $desiredOccupations;

    private $prePersisted = false;

    /**
     * @ORM\OneToMany(targetEntity=UserOccupation::class, mappedBy="user", orphanRemoval=true)
     */
    private $userOccupations;

    /**
     * @ORM\OneToMany(targetEntity=UserSkill::class, mappedBy="user", orphanRemoval=true)
     */
    private $userSkills;

    /**
     * @ORM\OneToMany(targetEntity=UserSearch::class, mappedBy="user", orphanRemoval=true)
     */
    private $userSearches;

    public function __construct()
    {
        $this->userOccupations = new ArrayCollection();
        $this->currentOccupations = new ArrayCollection();
        $this->previousOccupations = new ArrayCollection();
        $this->desiredOccupations = new ArrayCollection();
        $this->userSkills = new ArrayCollection();
        $this->userSearches = new ArrayCollection();
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
            'dateOfBirth'
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

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }
    public function setDateOfBirth(\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;
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

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getCompletion(): int
    {
        return $this->completion;
    }

    public function setCompletion(int $completion): self
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
     * @return Collection|UserTraining[]
     */
    public function getFollowedTrainings(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isFollowed', 1));

        return $this->userTrainings->matching($criteria);
    }

    /**
     * @return Collection|UserTraining[]
     */
    public function getInterestingForMeTrainings(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isInterestingForMe', 1));

        return $this->userTrainings->matching($criteria);
    }

    public function addUserTraining(UserTraining $userTraining): self
    {
        if (!$this->userTrainings->contains($userTraining)) {
            $this->userTrainings[] = $userTraining;
        }

        return $this;
    }

    public function removeUserTraining(UserTraining $userTraining): self
    {
        $this->userTrainings->removeElement($userTraining);

        return $this;
    }

    /**
     * @return Collection|UserTraining[]
     */
    public function getUserTrainings(): Collection
    {
        return $this->userTrainings;
    }

    /**
     * @return Collection|UserSkill[]
     */
    public function getUserSkills(): Collection
    {
        return $this->userSkills;
    }

    public function addUserSkill(UserSkill $userSkill): self
    {
        if (!$this->userSkills->contains($userSkill)) {
            $this->userSkills[] = $userSkill;
            $userSkill->setUser($this);
        }

        return $this;
    }

    public function removeUserSkill(UserSkill $userSkill): self
    {
        if ($this->userSkills->removeElement($userSkill)) {
            // set the owning side to null (unless already changed)
            if ($userSkill->getUser() === $this) {
                $userSkill->setUser(null);
            }
        }

        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): self
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function getIsSuspended(): ?bool
    {
        return $this->isSuspended;
    }

    public function setIsSuspended(bool $isSuspended): self
    {
        $this->isSuspended = $isSuspended;

        return $this;
    }

    public function getIsSuspected(): ?bool
    {
        return $this->isSuspected;
    }

    public function setIsSuspected(bool $isSuspected): self
    {
        $this->isSuspected = $isSuspected;

        return $this;
    }

    public function getIsSearchesKept(): ?bool
    {
        return $this->isSearchesKept;
    }

    public function setIsSearchesKept(bool $isSearchesKept): self
    {
        $this->isSearchesKept = $isSearchesKept;

        return $this;
    }

    public function getUpToDistance(): ?int
    {
        return $this->upToDistance;
    }

    public function setUpToDistance(int $upToDistance): self
    {
        $this->upToDistance = $upToDistance;

        return $this;
    }

    public function getIsListeningPosition(): ?bool
    {
        return $this->isListeningPosition;
    }

    public function setIsListeningPosition(bool $isListeningPosition): self
    {
        $this->isListeningPosition = $isListeningPosition;

        return $this;
    }

    public function getProfessionalExperience(): ?int
    {
        return $this->professionalExperience;
    }

    public function setProfessionalExperience(int $professionalExperience): self
    {
        $this->professionalExperience = $professionalExperience;

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCodeCreatedAt(): ?\DateTimeInterface
    {
        return $this->codeCreatedAt;
    }
    public function setCodeCreatedAt(\DateTimeInterface $codeCreatedAt): self
    {
        $this->codeCreatedAt = $codeCreatedAt;
        return $this;
    }

    /**
     * @return Collection|UserSearch[]
     */
    public function getUserSearches(): Collection
    {
        return $this->userSearches;
    }

    public function addUserSearch(UserSearch $userSearch): self
    {
        if (!$this->userSearches->contains($userSearch)) {
            $this->userSearches[] = $userSearch;
            $userSearch->setUser($this);
        }

        return $this;
    }

    public function removeUserSearch(UserSearch $userSearch): self
    {
        if ($this->userSearches->removeElement($userSearch)) {
            // set the owning side to null (unless already changed)
            if ($userSearch->getUser() === $this) {
                $userSearch->setUser(null);
            }
        }

        return $this;
    }
}
