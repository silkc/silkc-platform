<?php

namespace App\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PositionRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PositionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ApiResource(
 *      collectionOperations={"get"},
 *      itemOperations={"get"},
 *      normalizationContext={"groups"={"position:read"}},
 *      denormalizationContext={"groups"={"position:write"}},
 *      attributes={
 *          "formats"={"json"}
 *     }
 * )
 */
class Position
{
    public const CURRENCY_EURO = 'euro';
    public const CURRENCY_ZLOTY = 'złoty';

    protected static $currencies = [
        self::CURRENCY_EURO     => self::CURRENCY_EURO,
        self::CURRENCY_ZLOTY    => self::CURRENCY_ZLOTY,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"position:read", "position:write"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @Groups({"position:read", "position:write"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id", nullable=true)
     * @Groups({"position:read", "position:write"})
     */
    private $creator;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"position:read", "position:write"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"position:read", "position:write"})
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"position:read", "position:write"})
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"position:read", "position:write"})
     */
    private $latitude;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"position:read", "position:write"})
     */
    private $description;

    /**
     * @ORM\Column(type="float", length=255, nullable=true)
     * @Groups({"position:read", "position:write"})
     */
    private $salary;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"default": "euro"}, columnDefinition="ENUM('euro', 'złoty')")
     */
    private $currency = self::CURRENCY_EURO;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"position:read", "position:write"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"position:read", "position:write"})
     */
    private $startAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"position:read", "position:write"})
     */
    private $endAt;

    /**
     * @ORM\ManyToMany(targetEntity=Skill::class)
     * @Groups({"position:read", "position:write"})
     */
    private $skills;

    /**
     * @ORM\ManyToOne(targetEntity=Occupation::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $occupation;

    /**
     * @ORM\Column(type="integer", options={"default": 0, "unsigned": true, "comment": "Champs dynamique pour calcul de pondération lors d'une recherche de formation"})
     */
    private $score = 0;

    /**
     * @ORM\Column(type="integer", options={"default": 0, "unsigned": true, "comment": "Champs dynamique pour calcul de pondération lors d'une recherche de formation"})
     */
    private $maxScore = 0;

    /**
     * @ORM\Column(type="integer", length=3, nullable=false, options={"default": 0, "unsigned": true})
     */
    private $completion = 0;

    /**
     * @ORM\Column(type="boolean", options={"unsigned": true, "default": 1})
     */
    private $isVisible = 1;

    /**
     * @ORM\Column(type="boolean", options={"unsigned": true, "default": 0})
     */
    private $isValidated = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validatedAt;

    /**
     * @ORM\Column(type="boolean", options={"unsigned": true, "default": 0})
     */
    private $isRejected = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $rejectedAt;

    /**
     * @ORM\Column(type="integer", options={"default": 0, "unsigned": true, "comment": "Champs dynamique pour calcul de pondération lors d'une recherche de formation"})
     */
    private $distance = 0;

    private $prePersisted = false;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true, "comment": "Est-ce que le recruteur a déjà envoyé un email à tous les utilisateurs concernés par ce poste"})
     */
    private $isSentToAffectedUsers = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentToAffectedUsersAt;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->createdAt = new \DateTime();

        if ($this->prePersisted)
            return;

        $position = $args->getObject();
        $this->_defineCompletion($position);
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        if ($this->prePersisted)
            return;

        $position = $args->getObject();
        $this->_defineCompletion($position);
    }

    protected function _defineCompletion(Position $position)
    {
        $toCompleteProperties = [
            'name',
            'location',
            'description',
            'salary',
        ];


        $completed = 0;
        foreach ($toCompleteProperties as $property) {
            if (property_exists($position, $property) && $position->{$property} !== NULL && !empty($position->{$property}))
                $completed++;
        }

        $completion = ($completed === 0) ? 0 : floor(($completed / count($toCompleteProperties)) * 100);
        $position->completion = $completion;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param  string $typeShortName
     * @return string
     */
    public static function getCurrencies(bool $flip = FALSE):array
    {
        return ($flip) ? array_flip(static::$currencies) : static::$currencies;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;

        return $this;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSalary(): ?string
    {
        return $this->salary;
    }

    public function setSalary(?string $salary): self
    {
        $this->salary = $salary;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        if (!in_array($currency, [self::CURRENCY_EURO, self::CURRENCY_ZLOTY]))
            throw new \InvalidArgumentException("Invalid currency");

        $this->currency = $currency;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * @return Collection|Skill[]
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    /**
     * @return Collection|Skill[]
     */
    public function setSkills($skills): Collection
    {
        return $this->skills = $skills;
    }

    public function addSkill(Skill $skill): self
    {
        if (!$this->skills->contains($skill)) {
            $this->skills[] = $skill;
        }

        return $this;
    }

    public function removeSkill(Skill $skill): self
    {
        if ($this->skills->contains($skill)) {
            $this->skills->removeElement($skill);
        }

        return $this;
    }

    public function getOccupation(): ?Occupation
    {
        return $this->occupation;
    }


    public function setOccupation(?Occupation $occupation): self
    {
        $this->occupation = $occupation;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getMaxScore(): ?int
    {
        return $this->maxScore;
    }

    public function setMaxScore(?int $maxScore): self
    {
        $this->maxScore = $maxScore;

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

    public function getIsVisible(): ?bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): self
    {
        $this->isVisible = $isVisible;

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

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeInterface $validatedAt): self
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getIsRejected(): ?bool
    {
        return $this->isRejected;
    }

    public function setIsRejected(bool $isRejected): self
    {
        $this->isRejected = $isRejected;

        return $this;
    }

    public function getRejectedAt(): ?\DateTimeInterface
    {
        return $this->rejectedAt;
    }

    public function setRejectedAt(?\DateTimeInterface $rejectedAt): self
    {
        $this->rejectedAt = $rejectedAt;

        return $this;
    }

    public function getIsSentToAffectedUsers(): ?bool
    {
        return $this->isSentToAffectedUsers;
    }

    public function setIsSentToAffectedUsers(bool $isSentToAffectedUsers): self
    {
        $this->isSentToAffectedUsers = $isSentToAffectedUsers;

        return $this;
    }

    public function getSentToAffectedUsersAt(): ?\DateTimeInterface
    {
        return $this->sentToAffectedUsersAt;
    }

    public function setSentToAffectedUsersAt(\DateTimeInterface $sentToAffectedUsersAt): self
    {
        $this->sentToAffectedUsersAt = $sentToAffectedUsersAt;

        return $this;
    }
}
