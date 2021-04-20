<?php

namespace App\Entity;

use App\Entity\Skill;
use App\Entity\Occupation;
use App\Repository\OccupationSkillRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OccupationSkillRepository::class)
 */
class OccupationSkill
{
    public const RELATION_TYPE_ESSENTIAL    = 'essential';
    public const RELATION_TYPE_OPTIONAL     = 'optional';
    public const SKILL_TYPE_KNOWLEDGE       = 'knowledge';
    public const SKILL_TYPE_SKILL           = 'skill/competence';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Occupation::class, inversedBy="occupationSkills")
     * @ORM\JoinColumn(nullable=false)
     */
    private $occupation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $relationType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $skillType;

    /**
     * @ORM\ManyToOne(targetEntity=Skill::class, inversedBy="skillOccupations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $skill;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSkill(): ?Skill
    {
        return $this->skill;
    }

    public function setSkill(?Skill $skill): self
    {
        $this->skill = $skill;

        return $this;
    }

    public function getRelationType(): ?string
    {
        return $this->relationType;
    }

    public function setRelationType(string $relationType): self
    {
        $this->relationType = $relationType;

        return $this;
    }

    public function getSkillType(): ?string
    {
        return $this->skillType;
    }

    public function setSkillType(string $skillType): self
    {
        $this->skillType = $skillType;

        return $this;
    }

    public function getSkillUri(): ?string
    {
        return $this->skillUri;
    }

    public function setSkillUri(string $skillUri): self
    {
        $this->skillUri = $skillUri;

        return $this;
    }
}
