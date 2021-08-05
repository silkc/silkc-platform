<?php

namespace App\Entity;

use App\Repository\UserOccupationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserOccupationRepository::class)
 */
class UserOccupation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userOccupations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Occupation::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $occupation;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     */
    private $isCurrent = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     */
    private $isPrevious = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     */
    private $isDesired = 0;

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

    public function getIsCurrent(): ?bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): self
    {
        $this->isCurrent = $isCurrent;

        return $this;
    }

    public function getIsPrevious(): ?bool
    {
        return $this->isPrevious;
    }

    public function setIsPrevious(bool $isPrevious): self
    {
        $this->isPrevious = $isPrevious;

        return $this;
    }

    public function getIsDesired(): ?bool
    {
        return $this->isDesired;
    }

    public function setIsDesired(bool $isDesired): self
    {
        $this->isDesired = $isDesired;

        return $this;
    }
}
