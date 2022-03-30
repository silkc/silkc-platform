<?php

namespace App\Entity;

use App\Repository\OccupationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Intl\Locale;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\Apip\Occupation\OccupationGetCollectionController;
use App\Controller\Apip\Occupation\OccupationMainGetCollectionController;

/**
 * @ORM\Entity(repositoryClass=OccupationRepository::class)
 * @ApiResource(
 *      collectionOperations={
 *          "get",
 *          "get_with_locale": {
 *              "normalization_context": {"groups": "occupation:read"},
 *              "denormalization_context": { "allow_extra_attributes": true },
 *              "method": "GET",
 *              "path": "/occupations/locale/{locale}",
 *              "controller": OccupationGetCollectionController::class,
 *              "openapi_context": {
 *                  "summary": "Récupère les occupations avec la traductions dans la langue indiquée",
 *                  "parameters": {},
 *                  "filters": {},
 *                  "pagination_enabled": false
 *              }
 *          },
 *          "get_main": {
 *              "normalization_context": {"groups": "occupation:read:main"},
 *              "denormalization_context": { "allow_extra_attributes": true },
 *              "method": "GET",
 *              "path": "/occupations/main/locale/{locale}",
 *              "controller": OccupationMainGetCollectionController::class,
 *              "openapi_context": {
 *                  "summary": "Récupère les occupations avec la traductions dans la langue indiquée",
 *                  "parameters": {},
 *                  "filters": {},
 *                  "pagination_enabled": false
 *              }
 *          }
 *      },
 *      itemOperations={"get"},
 *      normalizationContext={"groups"={"occupation:read"}},
 *      denormalizationContext={"groups"={"occupation:write"}},
 *      attributes={
 *          "formats"={"json"},
 *          "order"={"id":"ASC"}
 *     }
 * )
 */
class Occupation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"occupation:read", "training:read", "occupation:read:main"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"occupation:read", "occupation:write"})
     */
    private $conceptType;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"occupation:read", "occupation:write"})
     */
    private $conceptUri;

    /**
     * @ORM\Column(type="text")
     * @Groups({"occupation:read", "occupation:write", "training:read", "occupation:read:main"})
     */
    private $preferredLabel;
    /**
     * @ORM\Column(type="text")
     * @Groups({"occupation:read", "occupation:write"})
     */
    private $altLabels;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"occupation:read", "occupation:write", "training:read", "occupation:read:main"})
     */
    private $hiddenLabels;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"occupation:read", "occupation:write"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"occupation:read", "occupation:write"})
     */
    private $modifiedAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"occupation:read", "occupation:write"})
     */
    private $regulatedProfessionNote;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"occupation:read", "occupation:write"})
     */
    private $scopeNote;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"occupation:read", "occupation:write"})
     */
    private $definition;

    /**
     * @ORM\Column(type="text")
     * @Groups({"occupation:read", "occupation:write"})
     */
    private $inScheme;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"occupation:read", "occupation:write"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"occupation:read", "occupation:write"})
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity=IscoGroup::class, inversedBy="occupations")
     * @Groups({"occupation:read"})
     */
    private $iscoGroup;

    /**
     * @ORM\OneToMany(targetEntity=OccupationTranslation::class, mappedBy="occupation", orphanRemoval=true)
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

    public function getPreferredLabel(string $locale = null): ?string
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

    public function getAltLabels(string $locale = null): ?string
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
            return $this->translations->matching($criteria)->first()->getAltLabels();

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

    public function setHiddenLabels(string $hiddenLabels): self
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

    public function getRegulatedProfessionNote(): ?string
    {
        return $this->regulatedProfessionNote;
    }

    public function setRegulatedProfessionNote(string $regulatedProfessionNote): self
    {
        $this->regulatedProfessionNote = $regulatedProfessionNote;

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

    public function getDescription(string $locale = null): ?string
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getIscoGroup(): ?IscoGroup
    {
        return $this->iscoGroup;
    }

    public function setIscoGroup(?IscoGroup $iscoGroup): self
    {
        $this->iscoGroup = $iscoGroup;

        return $this;
    }

    /**
     * @return Collection|OccupationTranslation[]
     */
    public function getTranslations(): Collection
    {
        /*$locale = Locale::getDefaultFallback();
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('locale', $locale));*/

        return $this->translations;
    }

    public function addTranslation(OccupationTranslation $occupationTranslation): self
    {
        if (!$this->translations->contains($occupationTranslation)) {
            $this->translations[] = $occupationTranslation;
            $occupationTranslation->setOccupation($this);
        }

        return $this;
    }

    public function removeTranslation(OccupationTranslation $occupationTranslation): self
    {
        if ($this->translations->removeElement($occupationTranslation)) {
            // set the owning side to null (unless already changed)
            if ($occupationTranslation->getOccupation() === $this) {
                $occupationTranslation->setOccupation(null);
            }
        }

        return $this;
    }
}
