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

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Project $project = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

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

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }


    /**
     * Get the value of user
     */
    public function getUser(): User
    {
        return $this->user;
    }



    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
