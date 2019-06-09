<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\Service\UploadHelper;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Пользователь с указанным email уже существует")
 */
class User implements UserInterface
{
    use TimestampableEntity;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $contacts;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Lot", mappedBy="author")
     */
    private $lots;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Lot", mappedBy="winner")
     */
    private $wonLots;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Bet", mappedBy="author")
     */
    private $bets;

    public function __construct()
    {
        $this->lots = new ArrayCollection();
        $this->wonLots = new ArrayCollection();
        $this->bets = new ArrayCollection();
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
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getContacts(): ?string
    {
        return $this->contacts;
    }

    public function setContacts(string $contacts): self
    {
        $this->contacts = $contacts;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return Collection|Lot[]
     */
    public function getLots(): Collection
    {
        return $this->lots;
    }

    public function addLot(Lot $lot): self
    {
        if (!$this->lots->contains($lot)) {
            $this->lots[] = $lot;
            $lot->setAuthor($this);
        }

        return $this;
    }

    public function removeLot(Lot $lot): self
    {
        if ($this->lots->contains($lot)) {
            $this->lots->removeElement($lot);
            // set the owning side to null (unless already changed)
            if ($lot->getAuthor() === $this) {
                $lot->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Lot[]
     */
    public function getWonLots(): Collection
    {
        return $this->wonLots;
    }

    public function addWonLot(Lot $wonLot): self
    {
        if (!$this->wonLots->contains($wonLot)) {
            $this->wonLots[] = $wonLot;
            $wonLot->setWinner($this);
        }

        return $this;
    }

    public function removeWonLot(Lot $wonLot): self
    {
        if ($this->wonLots->contains($wonLot)) {
            $this->wonLots->removeElement($wonLot);
            // set the owning side to null (unless already changed)
            if ($wonLot->getWinner() === $this) {
                $wonLot->setWinner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Bet[]
     */
    public function getBets(): Collection
    {
        return $this->bets;
    }

    public function addBet(Bet $bet): self
    {
        if (!$this->bets->contains($bet)) {
            $this->bets[] = $bet;
            $bet->setAuthor($this);
        }

        return $this;
    }

    public function removeBet(Bet $bet): self
    {
        if ($this->bets->contains($bet)) {
            $this->bets->removeElement($bet);
            // set the owning side to null (unless already changed)
            if ($bet->getAuthor() === $this) {
                $bet->setAuthor(null);
            }
        }

        return $this;
    }
    
    public function getAvatarPath(): string
    {
        if ($this->getAvatar()) {
            return UploadHelper::AVATAR . '/' . $this->getAvatar();
//            return 'uploads/avatar/' . $this->getAvatar();
        }
        
        return '';
    }
}
