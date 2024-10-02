<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

<<<<<<< HEAD

=======
>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private ?string $_id_project = null;

    #[ORM\Column(length: 255)]
    private ?string $name_project = null;

    #[ORM\Column(length: 255)]
    private ?string $description_project = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $deadline = null;

<<<<<<< HEAD

=======
  
>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="project")
     */
    private Collection $tasks;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

<<<<<<< HEAD
    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

=======
    public function __construct() {
        $this->tasks = new ArrayCollection();
    }

  

>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3
    public function getIdProject(): ?string
    {
        return $this->_id_project;
    }

    public function setIdProject(string $_id_project): static
    {
        $this->_id_project = $_id_project;

        return $this;
    }

    public function getNameProject(): ?string
    {
        return $this->name_project;
    }

    public function setNameProject(string $name_project): static
    {
        $this->name_project = $name_project;

        return $this;
    }

    public function getDescriptionProject(): ?string
    {
        return $this->description_project;
    }

    public function setDescriptionProject(string $description_project): static
    {
        $this->description_project = $description_project;

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

    /**
     * Get the value of user
<<<<<<< HEAD
     */
=======
     */ 
>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3
    public function getUser(): User
    {
        return $this->user;
    }
    /**
     * Set the value of user
     *
     * @return  self
<<<<<<< HEAD
     */
=======
     */ 
>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3
    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }


    /**
     * Get the value of tasks
<<<<<<< HEAD
     */
=======
     */ 
>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Set the value of tasks
     *
     * @return  self
<<<<<<< HEAD
     */
=======
     */ 
>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * Get the value of _id_project
<<<<<<< HEAD
     */
=======
     */ 
>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3
    public function get_id_project()
    {
        return $this->_id_project;
    }

    /**
     * Get the value of deadline
<<<<<<< HEAD
     */
=======
     */ 
>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Set the value of deadline
     *
     * @return  self
<<<<<<< HEAD
     */
=======
     */ 
>>>>>>> 14eebd5ba7c25b893878995ddc8e4636279492e3
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;

        return $this;
    }
    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setProject($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            // Set the owning side to null (unless already changed)
            if ($task->getProject() === $this) {
                $task->setProject(null);
            }
        }

        return $this;
    }
}
