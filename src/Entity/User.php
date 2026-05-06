<?php

namespace App\Entity;

use App\Enum\InvestmentGoal;
use App\Enum\PreferredIndustry;
use App\Enum\RiskTolerance;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Deprecated;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true, index: true)]
    private ?string $fullName = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(nullable: true, enumType: InvestmentGoal::class)]
    private ?InvestmentGoal $investmentGoal = null;

    #[ORM\Column(nullable: true, enumType: RiskTolerance::class)]
    private ?RiskTolerance $riskTolerance = null;

    #[ORM\Column(nullable: true, enumType: PreferredIndustry::class)]
    private ?PreferredIndustry $preferredIndustry = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $otpHash = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $otpExpiresAt = null;

    /**
     * @var Collection<int, WatchlistItem>
     */
    #[ORM\OneToMany(targetEntity: WatchlistItem::class, mappedBy: 'user', cascade: ['remove'], orphanRemoval: true)]
    private Collection $watchlistItems;

    /**
     * @var Collection<int, Alert>
     */
    #[ORM\OneToMany(targetEntity: Alert::class, mappedBy: 'user', cascade: ['remove'], orphanRemoval: true)]
    private Collection $alerts;

    public function __construct()
    {
        $this->watchlistItems = new ArrayCollection();
        $this->alerts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

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
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[Deprecated(message: 'To be removed when upgrading to Symfony 8')]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getInvestmentGoal(): ?InvestmentGoal
    {
        return $this->investmentGoal;
    }

    public function setInvestmentGoal(?InvestmentGoal $investmentGoal): static
    {
        $this->investmentGoal = $investmentGoal;

        return $this;
    }

    public function getRiskTolerance(): ?RiskTolerance
    {
        return $this->riskTolerance;
    }

    public function setRiskTolerance(?RiskTolerance $riskTolerance): static
    {
        $this->riskTolerance = $riskTolerance;

        return $this;
    }

    public function getPreferredIndustry(): ?PreferredIndustry
    {
        return $this->preferredIndustry;
    }

    public function setPreferredIndustry(?PreferredIndustry $preferredIndustry): static
    {
        $this->preferredIndustry = $preferredIndustry;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getOtpHash(): ?string
    {
        return $this->otpHash;
    }

    public function setOtpHash(?string $otpHash): static
    {
        $this->otpHash = $otpHash;

        return $this;
    }

    public function getOtpExpiresAt(): ?DateTimeImmutable
    {
        return $this->otpExpiresAt;
    }

    public function setOtpExpiresAt(?DateTimeImmutable $otpExpiresAt): static
    {
        $this->otpExpiresAt = $otpExpiresAt;

        return $this;
    }

    public function clearOtp(): void
    {
        $this->setOtpHash(null);
        $this->setOtpExpiresAt(null);
    }

    /**
     * @return Collection<int, WatchlistItem>
     */
    public function getWatchlistItems(): Collection
    {
        return $this->watchlistItems;
    }

    public function addWatchlistItem(WatchlistItem $item): static
    {
        if (!$this->watchlistItems->contains($item)) {
            $this->watchlistItems->add($item);
            $item->setUser($this);
        }

        return $this;
    }

    public function removeWatchlistItem(WatchlistItem $item): static
    {
        if ($this->watchlistItems->removeElement($item) && $item->getUser() === $this) {
            // set the owning side to null (unless already changed)
            $item->setUser(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Alert>
     */
    public function getAlerts(): Collection
    {
        return $this->alerts;
    }

    public function addAlert(Alert $alert): static
    {
        if (!$this->alerts->contains($alert)) {
            $this->alerts->add($alert);
            $alert->setUser($this);
        }

        return $this;
    }

    public function removeAlert(Alert $alert): static
    {
        if ($this->alerts->removeElement($alert)) {
            // set the owning side to null (unless already changed)
            if ($alert->getUser() === $this) {
                $alert->setUser(null);
            }
        }

        return $this;
    }
}
