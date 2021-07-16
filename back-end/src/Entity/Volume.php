<?php

namespace App\Entity;

use App\Repository\VolumeRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=VolumeRepository::class)
 */
class Volume
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Ce champ doit être renseigné.")
     */
    private $number;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;


    /**
     * @ORM\ManyToOne(targetEntity=Manga::class, inversedBy="volumes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manga;

    /**
     * @ORM\OneToMany(targetEntity=UserVolume::class, mappedBy="volume", orphanRemoval=true)
     */
    private $users;


    public function __construct()
    {
        
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

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


    public function getManga(): ?Manga
    {
        return $this->manga;
    }

    public function setManga(?Manga $manga): self
    {
        $this->manga = $manga;

        return $this;
    }

    /**
     * @return Collection|UserVolume[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(UserVolume $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setVolume($this);
        }

        return $this;
    }

    public function removeUser(UserVolume $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getVolume() === $this) {
                $user->setVolume(null);
            }
        }

        return $this;
    }
}
