<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get:project", "get:category", "get:technology", "get:customer"})
     * 
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"get:project", "get:category", "get:technology", "get:customer"})
     * 
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"get:project", "get:category", "get:technology", "get:customer"})
     * 
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Project", inversedBy="categories")
     * @Groups({"get:category"})
     */
    private $projects;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get:project", "get:technology", "get:customer"})
     * 
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get:project", "get:technology", "get:customer"})
     */
    private $updated_at;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

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
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
        }

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
}
