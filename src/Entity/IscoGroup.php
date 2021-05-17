<?php

namespace App\Entity;

use App\Repository\IscoGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiResource;


/**
 * @ORM\Entity(repositoryClass=IscoGroupRepository::class)
 * @ApiResource(
 *      normalizationContext={"groups"={"isco_group:read"}},
 *      denormalizationContext={"groups"={"isco_group:write"}}
 * )
 */
class IscoGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"isco_group:read", "occupation:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $conceptType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $conceptUri;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @ORM\Column(type="text")
     */
    private $preferredLabel;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $altLabels;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $inScheme;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Occupation::class, mappedBy="iscoGroup")
     */
    private $occupations;

    public function __construct()
    {
        $this->occupations = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getPreferredLabel(): ?string
    {
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

    public function setAltLabels(?string $altLabels): self
    {
        $this->altLabels = $altLabels;

        return $this;
    }

    public function getInScheme(): ?string
    {
        return $this->inScheme;
    }

    public function setInScheme(?string $inScheme): self
    {
        $this->inScheme = $inScheme;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Occupation[]
     */
    public function getOccupations(): Collection
    {
        return $this->occupations;
    }

    public function addOccupation(Occupation $occupation): self
    {
        if (!$this->occupations->contains($occupation)) {
            $this->occupations[] = $occupation;
            $occupation->setIscoGroup($this);
        }

        return $this;
    }

    public function removeOccupation(Occupation $occupation): self
    {
        if ($this->occupations->removeElement($occupation)) {
            // set the owning side to null (unless already changed)
            if ($occupation->getIscoGroup() === $this) {
                $occupation->setIscoGroup(null);
            }
        }

        return $this;
    }
}
