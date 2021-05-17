<?php

namespace App\Entity;

use App\Repository\TrainingSessionSkillRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrainingSessionSkillRepository::class)
 */
class TrainingSessionSkill
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TrainingSession::class, inversedBy="trainingSessionSkills")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trainingSession;

    /**
     * @ORM\ManyToOne(targetEntity=Skill::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $skill;

    /**
     * @ORM\Column(type="boolean", options={"unsigned": true, "default": 0})
     */
    private $isRequired = 0;

    /**
     * @ORM\Column(type="boolean", options={"unsigned": true, "default": 0})
     */
    private $isToAcquire = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrainingSession(): ?Training
    {
        return $this->training;
    }

    public function setTrainingSession(?Training $training): self
    {
        $this->training = $training;

        return $this;
    }

    public function getSkill(): ?Skill
    {
        return $this->skill;
    }

    public function setSkill(?Skill $skill): self
    {
        $this->skill = $skill;

        return $this;
    }

    public function getIsRequired(): ?bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    public function getIsToAcquire(): ?bool
    {
        return $this->isToAcquire;
    }

    public function setIsToAcquire(bool $isToAcquire): self
    {
        $this->isToAcquire = $isToAcquire;

        return $this;
    }
}
