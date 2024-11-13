<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private ?string $_id_task = null;


    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: '_project_id', referencedColumnName: '_id_project')]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: "_user_id", referencedColumnName: "_user_id", nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $name_task = null;

    #[ORM\Column(length: 255)]
    private ?string $description_task = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isUrgent = false;

    public function __construct() {}

    public function getIdTask(): ?string
    {
        return $this->_id_task;
    }

    public function setIdTask(string $_id_task): static
    {
        $this->_id_task = $_id_task;

        return $this;
    }

    public function getNameTask(): ?string
    {
        return $this->name_task;
    }

    public function setNameTask(string $name_task): static
    {
        $this->name_task = $name_task;

        return $this;
    }

    public function getDescriptionTask(): ?string
    {
        return $this->description_task;
    }

    public function setDescriptionTask(string $description_task): static
    {
        $this->description_task = $description_task;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    // Change this method to return a single Project
    public function getProject(): ?Project
    {
        return $this->project;
    }
    /**
     * @param Project|null $project
     */
    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }



    public function isUrgent(): bool
    {
        return $this->isUrgent;
    }
    public function setIsUrgent(bool $isUrgent): self
    {
        $this->isUrgent = $isUrgent;
        return $this;
    }
}
