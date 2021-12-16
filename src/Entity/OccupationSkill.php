<?php

namespace App\Entity;

use App\Entity\Skill;
use App\Entity\Occupation;
use App\Repository\OccupationSkillRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\Apip\OccupationSkill\OccupationSkillGetCollectionController;

/**
 * @ORM\Entity(repositoryClass=OccupationSkillRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"occupationSkill:read"}},
 *      collectionOperations={
 *          "get",
 *     },
 *      itemOperations={"get"},
 *      attributes={
 *          "formats"={"json"},
 *          "pagination_items_per_page": 10000,
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *     "occupation": "exact"
 * })
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
     * @Groups({"occupationSkill:read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Occupation::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"occupationSkill:read"})
     */
    private $occupation;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"occupationSkill:read"})
     */
    private $relationType;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"occupationSkill:read"})
     */
    private $skillType;

    /**
     * @ORM\ManyToOne(targetEntity=Skill::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"occupationSkill:read"})
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
}
