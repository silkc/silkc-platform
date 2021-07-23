<?php

namespace App\Entity;

use App\Repository\UserSearchRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserSearchRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class UserSearch
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userSearches")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Occupation::class)
     */
    private $occupation;

    /**
     * @ORM\ManyToOne(targetEntity=Skill::class)
     */
    private $skill;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default": "CURRENT_TIMESTAMP"})
     * @var string A "Y-m-d H:i:s" formatted value
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer", length=3, nullable=false, options={"default": 0, "unsigned": true})
     */
    private $countResults = 0;

    /**
     * Champs virtuel, uniquement rempli par hydratation en utilisant un JOIN et ResultSetMapping
     * @ORM\Column(type="integer", length=3, nullable=false, options={"default": 0, "unsigned": true})
     */
    private $countSearches = 0;

    /**
     * @ORM\Column(type="boolean", options={"unsigned": true, "default": 1})
     */
    private $isActive = 1;

    /**
     * @ORM\PrePersist
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCountResults(): ?int
    {
        return $this->countResults;
    }

    public function setCountResults(int $countResults): self
    {
        $this->countResults = $countResults;

        return $this;
    }

    public function getCountSearches(): ?int
    {
        return $this->countSearches;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
}
