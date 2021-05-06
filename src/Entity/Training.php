<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TrainingRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=TrainingRepository::class)
 * @ApiResource(
 *      collectionOperations={"get"},
 *      itemOperations={"get"},
 *      attributes={
 *          "formats"={"json"}
 *     }
 * )
 */
class Training
{
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

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $duration;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endAt;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     */
    private $hasSessions = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     */
    private $isOnline = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     */
    private $isOnlineMonitored = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     */
    private $isPresential = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $files;

    /**
     * @ORM\OneToMany(targetEntity=TrainingSession::class, mappedBy="training", orphanRemoval=true)
     */
    private $sessions;

    /**
     * @ORM\OneToMany(targetEntity=TrainingSkill::class, mappedBy="training", orphanRemoval=true, fetch="EAGER")
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

    public function __construct()
    {
        $this->requiredSkills = new ArrayCollection();
        $this->toAcquireSkills = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->trainingSkills = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
        $this->isOnline = $isOnlineMonitored;

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

    public function getOccupation(): ?occupation
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
}
