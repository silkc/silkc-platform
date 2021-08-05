<?php

namespace App\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TrainingRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TrainingRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ApiResource(
 *      collectionOperations={"get"},
 *      itemOperations={"get"},
 *      normalizationContext={"groups"={"training:read"}},
 *      denormalizationContext={"groups"={"training:write"}},
 *      attributes={
 *          "formats"={"json"}
 *     }
 * )
 */
class Training
{
    public const CURRENCY_EURO = 'euro';
    public const CURRENCY_ZLOTY = 'złoty';

    protected static $currencies = [
        self::CURRENCY_EURO => 'Euro',
        self::CURRENCY_ZLOTY => 'Złoty',
    ];

    /**
     * Coéfficient de pondération pour la recherche de formation via formulaire
     * par correspondance occupation-training
     */
    public const SEARCH_OCCUPATION_COEFFICIENT = 100;
    /**
     * Coéfficient de pondération pour la recherche de formation via formulaire
     * par correspondance skill-training de type "requis" ou "optionnal"
     */
    public const SEARCH_SKILL_COEFFICIENT = 10;
    public const SEARCH_OPTIONAL_SKILL_COEFFICIENT = 5;
    /**
     * Coéfficient de pondération pour la recherche de formation via formulaire
     * par correspondance skill-training de type "non-requis" ou "optionnal"
     */
    public const SEARCH_KNOWLEDGE_COEFFICIENT = 2;
    public const SEARCH_OPTIONAL_KNOWLEDGE_COEFFICIENT = 1;

    public const SEARCH_ACQUIRED_REQUIRED_SKILL_COEFFICIENT = 2;
    public const SEARCH_NOT_ACQUIRED_REQUIRED_SKILL_COEFFICIENT = 20;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"training:read", "training:write"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @Groups({"training:read", "training:write"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id", nullable=true)
     * @Groups({"training:read", "training:write"})
     */
    private $creator;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"training:read", "training:write"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"training:read", "training:write"})
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"training:read", "training:write"})
     */
    private $duration;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"training:read", "training:write"})
     */
    private $description;

    /**
     * @ORM\Column(type="float", length=255, nullable=true)
     * @Groups({"training:read", "training:write"})
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"default": "euro"}, columnDefinition="ENUM('euro', 'złoty')")
     */
    private $currency = self::CURRENCY_EURO;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"training:read", "training:write"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"training:read", "training:write"})
     */
    private $startAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"training:read", "training:write"})
     */
    private $endAt;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     * @Groups({"training:read", "training:write"})
     */
    private $hasSessions = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     * @Groups({"training:read", "training:write"})
     */
    private $isOnline = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     * @Groups({"training:read", "training:write"})
     */
    private $isOnlineMonitored = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     * @Groups({"training:read", "training:write"})
     */
    private $isPresential = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"training:read", "training:write"})
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"training:read", "training:write"})
     */
    private $files;

    /**
     * @ORM\OneToMany(targetEntity=TrainingSession::class, mappedBy="training", orphanRemoval=true)
     */
    private $sessions;

    /**
     * @ORM\OneToMany(targetEntity=TrainingSkill::class, mappedBy="training", orphanRemoval=true, fetch="EAGER")
     * @Groups({"training:read", "training:write"})
     */
    private $trainingSkills;

    private $requiredSkills;
    private $toAcquireSkills;

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

    private $prePersisted = false;

    /**
     * @ORM\OneToMany(targetEntity=TrainingFeedback::class, mappedBy="training")
     */
    private $trainingFeedback;

    public function __construct()
    {
        $this->requiredSkills = new ArrayCollection();
        $this->toAcquireSkills = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->trainingSkills = new ArrayCollection();
        $this->trainingFeedback = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->createdAt = new \DateTime();

        if ($this->prePersisted)
            return;

        $training = $args->getObject();
        $this->_defineCompletion($training);
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        if ($this->prePersisted)
            return;

        $training = $args->getObject();
        $this->_defineCompletion($training);
    }

    protected function _defineCompletion(Training $training)
    {
        $toCompleteProperties = [
            'name',
            'location',
            'duration',
            'description',
            'price',
            'url'
        ];


        $completed = 0;
        foreach ($toCompleteProperties as $property) {
            if (property_exists($training, $property) && $training->{$property} !== NULL && !empty($training->{$property}))
                $completed++;
        }

        $completion = ($completed === 0) ? 0 : floor(($completed / count($toCompleteProperties)) * 100);
        $training->completion = $completion;
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

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

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

    public function getHasSessions(): ?bool
    {
        return $this->hasSessions;
    }

    public function setHasSessions(bool $hasSessions): self
    {
        $this->hasSessions = $hasSessions;

        return $this;
    }

    public function getIsOnline(): ?bool
    {
        return $this->isOnline;
    }

    public function setIsOnlineMonitored(bool $isOnlineMonitored): self
    {
        $this->isOnlineMonitored = $isOnlineMonitored;

        return $this;
    }

    public function getIsOnlineMonitored(): ?bool
    {
        return $this->isOnlineMonitored;
    }

    public function setIsOnline(bool $isOnline): self
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    public function getIsPresential(): ?bool
    {
        return $this->isPresential;
    }

    public function setIsPresential(bool $isPresential): self
    {
        $this->isPresential = $isPresential;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getFiles(): ?string
    {
        return $this->files;
    }

    public function setFiles(string $files): self
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @return Collection|TrainingSession[]
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(TrainingSession $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions[] = $session;
            $session->setTraining($this);
        }

        return $this;
    }

    public function removeSession(TrainingSession $session): self
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getTraining() === $this) {
                $session->setTraining(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TrainingSkill[]
     */
    public function getTrainingSkills(): Collection
    {
        return $this->trainingSkills;
    }

    /**
     * @return Collection|TrainingSkill[]
     */
    public function setTrainingSkills($trainingsSkills): Collection
    {
        return $this->trainingSkills = $trainingsSkills;
    }

    public function addTrainingSkill(TrainingSkill $trainingSkill): self
    {
        if (!$this->trainingSkills->contains($trainingSkill)) {
            $this->trainingSkills[] = $trainingSkill;
            $trainingSkill->setTraining($this);
        }

        return $this;
    }

    public function removeTrainingSkill(TrainingSkill $trainingSkill): self
    {
        if ($this->trainingSkills->removeElement($trainingSkill)) {
            // set the owning side to null (unless already changed)
            if ($trainingSkill->getTraining() === $this) {
                $trainingSkill->setTraining(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TrainingSkill[]
     */
    public function getRequiredSkills(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isRequired', 1));

        return $this->trainingSkills->matching($criteria);
    }

    /**
     * @return Collection|TrainingSkill[]
     */
    public function getToAcquireSkills(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isToAcquire', 1));

        return $this->trainingSkills->matching($criteria);
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

    /**
     * @return Collection|TrainingFeedback[]
     */
    public function getTrainingFeedback(): Collection
    {
        return $this->trainingFeedback;
    }

    public function addTrainingFeedback(TrainingFeedback $trainingFeedback): self
    {
        if (!$this->trainingFeedback->contains($trainingFeedback)) {
            $this->trainingFeedback[] = $trainingFeedback;
            $trainingFeedback->setTraining($this);
        }

        return $this;
    }

    public function removeTrainingFeedback(TrainingFeedback $trainingFeedback): self
    {
        if ($this->trainingFeedback->removeElement($trainingFeedback)) {
            // set the owning side to null (unless already changed)
            if ($trainingFeedback->getTraining() === $this) {
                $trainingFeedback->setTraining(null);
            }
        }

        return $this;
    }
}
