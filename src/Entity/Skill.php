<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Intl\Locale;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Apip\Skill\SkillGetCollectionController;
use App\Controller\Apip\Skill\SkillMainGetCollectionController;

/**
 * @ORM\Entity(repositoryClass=SkillRepository::class)
 * @ApiResource(
 *      collectionOperations={
 *          "get",
 *          "get_with_locale": {
 *              "normalization_context": {"groups": "skill:read"},
 *              "denormalization_context": { "allow_extra_attributes": true },
 *              "method": "GET",
 *              "path": "/skills/locale/{locale}",
 *              "controller": SkillGetCollectionController::class,
 *              "openapi_context": {
 *                  "summary": "Récupère les skills avec la traductions dans la langue indiquée",
 *                  "parameters": {},
 *                  "filters": {},
 *                  "pagination_enabled": false
 *              }
 *          },
 *          "get_main": {
 *              "normalization_context": {"groups": "skill:read:main"},
 *              "denormalization_context": { "allow_extra_attributes": true },
 *              "method": "GET",
 *              "path": "/skills/main/locale/{locale}",
 *              "controller": SkillMainGetCollectionController::class,
 *              "openapi_context": {
 *                  "summary": "Récupère les skills avec la traductions dans la langue indiquée",
 *                  "parameters": {},
 *                  "filters": {},
 *                  "pagination_enabled": false
 *              }
 *          }
 *      },
 *      itemOperations={"get"},
 *      normalizationContext={"groups"={"skill:read", "occupationSkill:read"}},
 *      denormalizationContext={"groups"={"skill:write", "occupationSkill:read"}},
 *      attributes={
 *          "formats"={"json"}
 *     }
 * )
 */
class Skill
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"occupation:read", "skill:read", "skill:read:main", "occupationSkill:read", "trainingSkill:read", "training:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"occupation:read", "skill:read", "occupationSkill:read", "trainingSkill:read", "training:read"})
     */
    private $conceptType;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"occupation:read", "skill:read", "occupationSkill:read", "trainingSkill:read", "training:read"})
     */
    private $conceptUri;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"occupation:read", "skill:read", "occupationSkill:read", "trainingSkill:read", "training:read"})
     */
    private $skillType;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"occupation:read", "skill:read", "occupationSkill:read", "trainingSkill:read", "training:read"})
     */
    private $reuseLevel;

    /**
     * @ORM\Column(type="text")
     * @Groups({"occupation:read", "skill:read", "skill:read:main", "occupationSkill:read", "trainingSkill:read", "training:read"})
     */
    private $preferredLabel;

    /**
     * @ORM\Column(type="text")
     */
    private $altLabels;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $hiddenLabels;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modifiedAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $scopeNote;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $definition;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $inScheme;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"occupation:read", "skill:read", "occupationSkill:read", "trainingSkill:read", "training:read"})
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=SkillTranslation::class, mappedBy="skill", orphanRemoval=true)
     * @Groups({"training:read", "occupationSkill:read"})
     */
    private $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConceptType(): ?string
    {
        return $this->conceptType;
    }

    public function setConceptType(string $conceptType): self
    {
        $this->conceptType = $conceptType;

        return $this;
    }

    public function getConceptUri(): ?string
    {
        return $this->conceptUri;
    }

    public function setConceptUri(string $conceptUri): self
    {
        $this->conceptUri = $conceptUri;

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

    public function getReuseLevel(): ?string
    {
        return $this->reuseLevel;
    }

    public function setReuseLevel(string $reuseLevel): self
    {
        $this->reuseLevel = $reuseLevel;

        return $this;
    }

    public function getPreferredLabel(): ?string
    {
        $locale = Locale::getDefaultFallback();
        if(isset($GLOBALS['request']) && $GLOBALS['request']) {
            $locale = $GLOBALS['request']->getLocale();    
        }
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('locale', $locale));

        if (
            $this->translations &&
            $this->translations instanceof Collection &&
            $this->translations->count() > 0 &&
            $this->translations->matching($criteria)->count() > 0
        )
            return $this->translations->matching($criteria)->first()->getPreferredLabel();

        return $this->preferredLabel;
    }

    public function setPreferredLabel(string $preferredLabel): self
    {
        $this->preferredLabel = $preferredLabel;

        return $this;
    }

    public function getAltLabels(): ?string
    {
        return $this->altLabels;
    }

    public function setAltLabels(string $altLabels): self
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(?\DateTimeInterface $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function getScopeNote(): ?string
    {
        return $this->scopeNote;
    }

    public function setScopeNote(?string $scopeNote): self
    {
        $this->scopeNote = $scopeNote;

        return $this;
    }

    public function getDefinition(): ?string
    {
        $locale = Locale::getDefaultFallback();
        if(isset($GLOBALS['request']) && $GLOBALS['request']) {
            $locale = $GLOBALS['request']->getLocale();    
        }
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('locale', $locale));

        if (
            $this->translations &&
            $this->translations instanceof Collection &&
            $this->translations->count() > 0 &&
            $this->translations->matching($criteria)->count() > 0
        )
            return $this->translations->matching($criteria)->first()->getDefinition();

        return $this->definition;
    }

    public function setDefinition(?string $definition): self
    {
        $this->definition = $definition;

        return $this;
    }

    public function getInScheme(): ?string
    {
        return $this->inScheme;
    }

    public function setInScheme(string $inScheme): self
    {
        $this->inScheme = $inScheme;

        return $this;
    }

    public function getDescription(): ?string
    {
        $locale = Locale::getDefaultFallback();
        if(isset($GLOBALS['request']) && $GLOBALS['request']) {
            $locale = $GLOBALS['request']->getLocale();    
        }
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('locale', $locale));

        if (
            $this->translations &&
            $this->translations instanceof Collection &&
            $this->translations->count() > 0 &&
            $this->translations->matching($criteria)->count() > 0
        )
            return $this->translations->matching($criteria)->first()->getDescription();

        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|SkillTranslation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(SkillTranslation $skillTranslation): self
    {
        if (!$this->translations->contains($skillTranslation)) {
            $this->translations[] = $skillTranslation;
            $skillTranslation->setSkill($this);
        }

        return $this;
    }

    public function removeTranslation(SkillTranslation $skillTranslation): self
    {
        if ($this->translations->removeElement($skillTranslation)) {
            // set the owning side to null (unless already changed)
            if ($skillTranslation->getSkill() === $this) {
                $skillTranslation->setSkill(null);
            }
        }

        return $this;
    }
}
