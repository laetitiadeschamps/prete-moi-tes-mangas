<?php

namespace App\Entity;

use App\Repository\MangaRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MangaRepository::class)
 */
class Manga
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"mangas"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Ce champ doit être renseigné.")
     * @Groups({"mangas", "users"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="Ce champ doit être renseigné.")
     * @Groups({"mangas", "users"})
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"mangas", "users"})
     */
    private $picture;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"mangas", "users"})
     */
    private $synopsis;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Ce champ doit être renseigné")
     * @Groups({"mangas", "users"})
     */
    private $volume_number;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class, mappedBy="mangas")
     * @Groups({"mangas"})
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity=Volume::class, mappedBy="manga", orphanRemoval=true)
     * @Groups({"mangas"})
     */
    private $volumes;


    public function __construct()
    {
       
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
        $this->categories = new ArrayCollection();
        $this->volumes = new ArrayCollection();
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(?string $synopsis): self
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getVolumeNumber(): ?int
    {
        return $this->volume_number;
    }

    public function setVolumeNumber(int $volume_number): self
    {
        $this->volume_number = $volume_number;

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

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->addManga($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            $category->removeManga($this);
        }

        return $this;
    }

    /**
     * @return Collection|Volume[]
     */
    public function getVolumes(): Collection
    {
        return $this->volumes;
    }

    public function addVolume(Volume $volume): self
    {
        if (!$this->volumes->contains($volume)) {
            $this->volumes[] = $volume;
            $volume->setManga($this);
        }

        return $this;
    }

    public function removeVolume(Volume $volume): self
    {
        if ($this->volumes->removeElement($volume)) {
            // set the owning side to null (unless already changed)
            if ($volume->getManga() === $this) {
                $volume->setManga(null);
            }
        }

        return $this;
    }
}
