<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("get:project")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups("get:project")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups("get:project")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("get:project")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("get:project")
     */
    private $updated_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("get:project")
     */
    private $link;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\File(
     *      maxSize = "10240k",
     *      mimeTypes={ "image/jpeg", "image/png", "image/jpg"},
     *      mimeTypesMessage = "Le type de fichier n'est pas valide..."
     * )
     * @Assert\NotBlank(message="Veuillez insérer une image pour le projet.")
     * @Groups("get:project")
     */
    private $thumbnail;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups("get:project")
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Technology", inversedBy="projects")
     * @Groups("get:project")
     */
    private $technologies;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="projects")
     * @Groups("get:project")
     */
    private $customer;

    public function __construct()
    {
        $this->technologies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

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
     * @return Collection|Technology[]
     */
    public function getTechnologies(): Collection
    {
        return $this->technologies;
    }

    public function addTechnology(Technology $technology): self
    {
        if (!$this->technologies->contains($technology)) {
            $this->technologies[] = $technology;
        }

        return $this;
    }

    public function removeTechnology(Technology $technology): self
    {
        if ($this->technologies->contains($technology)) {
            $this->technologies->removeElement($technology);
        }

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
}
