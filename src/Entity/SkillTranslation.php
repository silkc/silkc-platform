<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\SkillTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SkillTranslationRepository::class)
 */
#[ApiResource]
class SkillTranslation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Skill::class, inversedBy="skillTranslations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $skill;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $locale;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $preferredLabel;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $altLabels;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $hiddenLabels;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $definition;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getPreferredLabel(): ?string
    {
        return $this->preferredLabel;
    }

    public function setPreferredLabel(?string $preferredLabel): self
    {
        $this->preferredLabel = $preferredLabel;

        return $this;
    }

    public function getAltLabels(): ?string
    {
        return $this->altLabels;
    }

    public function setAltLabels(?string $altLabels): self
    {
        $this->altLabels = $altLabels;

        return $this;
    }

    public function getHiddenLabels(): ?string
    {
        return $this->hiddenLabels;
    }

    public function setHiddenLabels(?string $hiddenLabels): self
    {
        $this->hiddenLabels = $hiddenLabels;

        return $this;
    }

    public function getDefinition(): ?string
    {
        return $this->definition;
    }

    public function setDefinition(?string $definition): self
    {
        $this->definition = $definition;

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
}
