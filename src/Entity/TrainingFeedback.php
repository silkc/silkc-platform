<?php

namespace App\Entity;

use App\Repository\TrainingFeedbackRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ORM\Entity(repositoryClass=TrainingFeedbackRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ApiResource(
 *      normalizationContext={"groups"={"training_feedback:read"}},
 *      denormalizationContext={"groups"={"training_feedback:write"}},
 *      attributes={
 *          "formats"={"json"},
 *          "order"={"id":"ASC"}
 *     }
 * )
 */
class TrainingFeedback
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Training::class, inversedBy="trainingFeedback")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"training_feedback:read", "training_feedback:write"})
     */
    private $training;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"training_feedback:read", "training_feedback:write"})
     */
    private $user;

    /**
     * @ORM\Column(type="integer", length=3, nullable=false, options={"default": 0, "unsigned": true})
     * @Groups({"training_feedback:read", "training_feedback:write"})
     */
    private $mark = 0;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"training_feedback:read", "training_feedback:write"})
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"training_feedback:read", "training_feedback:write"})
     */
    private $createdAt;

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

    public function getTraining(): ?Training
    {
        return $this->training;
    }

    public function setTraining(?Training $training): self
    {
        $this->training = $training;

        return $this;
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

    public function getMark(): ?int
    {
        return $this->mark;
    }

    public function setMark(int $mark): self
    {
        $this->mark = $mark;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
