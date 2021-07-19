<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email", "pseudo"}, message="L'email et le pseudo doivent être uniques")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"chats", "one-chat", "users", "search"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email(
     *     message = "'{{ value }}' n'est pas un email valide."
     * )
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     * @Groups({"users"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     * @Groups({"users"})
     */
    private $roles = [];

    /**
     * @var string Thse hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     *  @Assert\Regex(
     *      pattern="/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$%_*|=&-])[A-Za-z\d@$%_*|=&-]{6,}$/",
     *      message="Le mot de passe doit faire au moins 6 caractères, comporter une majuscule, une minuscule, un chiffre et un caractère spécial parmi les suivants : @$%_*|=-"
     *  )
     * @Groups({"users"})
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     * @Assert\Length(
     *      min = 4,
     *      max = 15,
     *      minMessage = "Votre pseudo doit faire au moins {{ limit }} caractères.",
     *      maxMessage = "Votre pseudo doit ne doit pas faire plus de {{ limit }} caractères."
     * )
     * @Groups({"chats", "one-chat", "users", "search"})
    */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     * @Assert\Length(   
     *      max = 50,
     *      maxMessage = "Votre nom doit faire moins de  {{ limit }} caractères."
     * )
     * @Groups({"users"})
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     * @Assert\Length(     
     *      max = 50,
     *      maxMessage = "Votre prénom doit faire moins de {{ limit }} caractères."
     * )
     * @Groups({"users"})
     */
    private $firstname;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"users"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"chats", "one-chat", "users"})
     */
    private $picture;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     * @Groups({"users"})
     */
    private $address;

    /**
     * @ORM\Column(type="smallint", options={"unsigned":true})
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     * @Assert\Regex(
     *      pattern="/^[0-9]{5}$/",
     *      message="Veuillez saisir un code postal valide."
     * )
     * @Groups({"users"})
     */
    private $zip_code;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     * @Groups({"users", "search"})
     */
    private $city;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"users"})
     */
    private $holiday_mode;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Regex("/^(0|1)$/")
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     * @Groups({"users"})
     */
    private $status;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     * @Groups({"users"})
     */
    private $latitude;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank(message="Ce champ ne peut pas être vide.")
     * @Groups({"users", "search"})
     */
    private $longitude;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;


    /**
     * @ORM\ManyToMany(targetEntity=Chat::class, inversedBy="users")
     * 
     */
    private $chats;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="author")
     * 
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity=UserVolume::class, mappedBy="user", orphanRemoval=true)
     * @Groups({"users"})
     */
    private $volumes;


    public function __construct()
    {
        $this->status = 1;
        $this->holiday_mode = false;
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
        $this->chats = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->volumes = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?int
    {
        return $this->zip_code;
    }

    public function setZipCode(int $zip_code): self
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getHolidayMode(): ?bool
    {
        return $this->holiday_mode;
    }

    public function setHolidayMode(bool $holiday_mode): self
    {
        $this->holiday_mode = $holiday_mode;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLatitude(): ?int
    {
        return $this->latitude;
    }

    public function setLatitude(int $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?int
    {
        return $this->longitude;
    }

    public function setLongitude(int $longitude): self
    {
        $this->longitude = $longitude;

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
     * @return Collection|Chat[]
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(Chat $chat): self
    {
        if (!$this->chats->contains($chat)) {
            $this->chats[] = $chat;
        }

        return $this;
    }

    public function removeChat(Chat $chat): self
    {
        $this->chats->removeElement($chat);

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setAuthor($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getAuthor() === $this) {
                $message->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserVolume[]
     */
    public function getVolumes(): Collection
    {
        return $this->volumes;
    }

    public function addVolume(UserVolume $volume): self
    {
        if (!$this->volumes->contains($volume)) {
            $this->volumes[] = $volume;
            $volume->setUser($this);
        }

        return $this;
    }

    public function removeVolume(UserVolume $volume): self
    {
        if ($this->volumes->removeElement($volume)) {
            // set the owning side to null (unless already changed)
            if ($volume->getUser() === $this) {
                $volume->setUser(null);
            }
        }

        return $this;
    }


}
