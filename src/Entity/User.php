<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 255)]
    private ?string $_id_user = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="user")
     */
    private ArrayCollection $projects;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="user")
     */
    private ArrayCollection $tasks;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name_user = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Email is required")]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_link = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contract = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Password is required")]
    private ?string $mdp = null;

    private ?string $confirm_mdp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $infos_user = null;


    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }

    public function getIdUser(): ?string
    {
        return $this->_id_user;
    }

    public function setIdUser(string $idUser): self
    {
        $this->_id_user = $idUser;

        return $this;
    }
    public function getProjects(): ArrayCollection
    {
        return $this->projects;
    }

    public function getTasks(): ArrayCollection
    {
        return $this->tasks;
    }

    public function setProjects(ArrayCollection $projects): self
    {
        $this->projects = $projects;

        return $this;
    }

    public function setTasks(ArrayCollection $tasks): self
    {
        $this->tasks = $tasks;

        return $this;
    }

    public function getNameUser(): ?string
    {
        return $this->name_user;
    }

    public function setNameUser(?string $name_user): static
    {
        $this->name_user = $name_user;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    // Implement the getPassword method required by PasswordAuthenticatedUserInterface
    public function getPassword(): string
    {
        // Assuming your User entity stores the password in a property named `mdp`
        return $this->mdp;
    }

    // Ensure you have a method to set the password as well
    public function setPassword(string $password): self
    {
        $this->mdp = $password;
        return $this;
    }


    /**
     * Get the value of _id_user
     */
    public function get_id_user()
    {
        return $this->_id_user;
    }

    /**
     * Get the value of confirm_mdp
     */
    public function getConfirm_mdp()
    {
        return $this->confirm_mdp;
    }

    /**
     * Set the value of confirm_mdp
     *
     * @return  self
     */
    public function setConfirm_mdp($confirm_mdp)
    {
        $this->confirm_mdp = $confirm_mdp;

        return $this;
    }

    /**
     * Get the value of infos_user
     */
    public function getInfos_user(): ?string
    {
        return $this->infos_user;
    }

    /**
     * Set the value of infos_user
     *
     * @return  self
     */
    public function setInfos_user(?string $infos_user): self
    {
        $this->infos_user = $infos_user;
        return $this;
    }
    // Méthodes pour les projets
    public function addProjects(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setUser($this);
        }

        return $this;
    }

    public function removeProjects(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            // Set the owning side to null (unless already changed)
            if ($project->getUser() === $this) {
                $project->setUser($this);
            }
        }

        return $this;
    }

    // Méthodes pour les tâches
    public function addTasks(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setUser($this);
        }

        return $this;
    }

    public function removeTasks(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            // Set the owning side to null (unless already changed)
            if ($task->getUser() === $this) {
                $task->setUser($this);
            }
        }

        return $this;
    }
    public function getRoles(): array
    {
        // Assurez-vous que chaque utilisateur a au moins le rôle "ROLE_USER"
        return ['ROLE_USER'];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        // On efface ici les données sensibles si nécessaire
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Get the value of image_link
     */
    public function getImage_link()
    {
        return $this->image_link;
    }

    /**
     * Set the value of image_link
     *
     * @return  self
     */
    public function setImage_link($image_link)
    {
        $this->image_link = $image_link;

        return $this;
    }

    /**
     * Get the value of role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the value of role
     *
     * @return  self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the value of adress
     */
    public function getAdress()
    {
        return $this->adress;
    }

    /**
     * Set the value of adress
     *
     * @return  self
     */
    public function setAdress($adress)
    {
        $this->adress = $adress;

        return $this;
    }

    /**
     * Get the value of contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Set the value of contract
     *
     * @return  self
     */
    public function setContract($contract)
    {
        $this->contract = $contract;

        return $this;
    }
}
