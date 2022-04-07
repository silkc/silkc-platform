<?php

namespace App\Entity;

use App\Repository\UserTrainingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserTrainingRepository::class)
 */
class UserTraining
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
     * @ORM\ManyToOne(targetEntity=Training::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $training;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     */
    private $isFollowed = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     */
    private $isInterestingForMe = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     */
    private $isLikedByMe = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": 0, "unsigned": true})
     */
    private $isDislikedByMe = 0;


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

    public function getTraining(): ?Training
    {
        return $this->training;
    }

    public function setTraining(?Training $training): self
    {
        $this->training = $training;

        return $this;
    }

    public function getIsFollowed(): ?bool
    {
        return $this->isFollowed;
    }

    public function setIsFollowed(bool $isFollowed): self
    {
        $this->isFollowed = $isFollowed;

        return $this;
    }

    public function getIsInterestingForMe(): ?bool
    {
        return $this->isInterestingForMe;
    }

    public function setIsInterestingForMe(bool $isInterestingForMe): self
    {
        $this->isInterestingForMe = $isInterestingForMe;

        return $this;
    }

    public function getIsLikedByMe(): ?bool
    {
        return $this->isLikedByMe;
    }

    public function setIsLikedByMe(bool $isLikedByMe): self
    {
        $this->isLikedByMe = $isLikedByMe;

        return $this;
    }

    public function getIsDislikedByMe(): ?bool
    {
        return $this->isDislikedByMe;
    }

    public function setIsDislikedByMe(bool $isDislikedByMe): self
    {
        $this->isDislikedByMe = $isDislikedByMe;

        return $this;
    }
}
