<?php

namespace App\Entity;

use App\Entity\Skill;
use App\Entity\Training;
use App\Repository\TrainingSkillRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrainingSkillRepository::class)
 */
class TrainingSkill
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Training::class, inversedBy="trainingSkills")
     * @ORM\JoinColumn(nullable=false)
     */
    private $training;

    /**
     * @ORM\ManyToOne(targetEntity=Skill::class, fetch="EAGER")
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

    public function getTraining(): ?Training
    {
        return $this->training;
    }

    public function setTraining(?Training $training): self
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
