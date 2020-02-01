<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;



/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 */
class Customer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get:project", "get:category", "get:technology", "get:customer"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"get:project", "get:category", "get:technology", "get:customer"})
     */
    private $name;

    /**
     * @Assert\File(
     *      maxSize = "10240k",
     *      mimeTypes={ "image/jpeg", "image/png", "image/jpg"},
     *      mimeTypesMessage = "Le type de fichier n'est pas valide..."
     * )
     * @Assert\NotBlank(message="Veuillez insérer le logo de l'entreprise correspondant au client.")
     * @ORM\Column(type="string", length=255)
     * @Groups({"get:project", "get:category", "get:technology", "get:customer"})
     */
    private $logo;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"get:project", "get:category", "get:technology", "get:customer"})
     */
    private $slug;

    /**
     * @ORM\Column(type="text")
     * @Groups({"get:project", "get:category", "get:technology", "get:customer"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get:project", "get:category", "get:technology", "get:customer"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get:project", "get:category", "get:technology", "get:customer"})
     */
    private $updated_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"get:project", "get:category", "get:technology", "get:customer"})
     */
    private $link;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="customer")
     * @Groups({"get:customer"})
     */
    private $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setCustomer($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            // set the owning side to null (unless already changed)
            if ($project->getCustomer() === $this) {
                $project->setCustomer(null);
            }
        }

        return $this;
    }
}
