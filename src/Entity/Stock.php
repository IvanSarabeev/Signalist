<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 10)]
    private ?string $symbol = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    private ?string $exchange = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $industry = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $logoUrl = null;

    #[ORM\Column(type: Types::STRING, length: 5, nullable: true)]
    private ?string $currency = null;

    // --- Cached Quote Fields (refreshed by a background job / Symfony Scheduler) ---

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 4, nullable: true)]
    private ?string $cachedPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 4, nullable: true)]
    private ?string $cachedChangePercent = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 4, nullable: true)]
    private ?string $cachedPreviousClose = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 4, nullable: true)]
    private ?string $cachedHigh = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 4, nullable: true)]
    private ?string $cachedLow = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE,nullable: true)]
    private ?\DateTimeImmutable $quoteCachedAt = null;

    /**
     * @var Collection<int, WatchlistItem>
     */
    #[ORM\OneToMany(targetEntity: WatchlistItem::class, mappedBy: 'stock', orphanRemoval: true)]
    private Collection $stock;

    /**
     * @var Collection<int, Alert>
     */
    #[ORM\OneToMany(targetEntity: Alert::class, mappedBy: 'stock')]
    private Collection $alerts;

    public function __construct()
    {
        $this->stock = new ArrayCollection();
        $this->alerts = new ArrayCollection();
    }

    // --- Relationships ---


    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getExchange(): ?string
    {
        return $this->exchange;
    }

    public function setExchange(?string $exchange): static
    {
        $this->exchange = $exchange;

        return $this;
    }

    public function getIndustry(): ?string
    {
        return $this->industry;
    }

    public function setIndustry(?string $industry): static
    {
        $this->industry = $industry;

        return $this;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): static
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCachedPrice(): ?string
    {
        return $this->cachedPrice;
    }

    public function setCachedPrice(?string $cachedPrice): static
    {
        $this->cachedPrice = $cachedPrice;

        return $this;
    }

    public function getCachedChangePercent(): ?string
    {
        return $this->cachedChangePercent;
    }

    public function setCachedChangePercent(?string $cachedChangePercent): static
    {
        $this->cachedChangePercent = $cachedChangePercent;

        return $this;
    }

    public function getCachedPreviousClose(): ?string
    {
        return $this->cachedPreviousClose;
    }

    public function setCachedPreviousClose(?string $cachedPreviousClose): static
    {
        $this->cachedPreviousClose = $cachedPreviousClose;

        return $this;
    }

    public function getCachedHigh(): ?string
    {
        return $this->cachedHigh;
    }

    public function setCachedHigh(?string $cachedHigh): static
    {
        $this->cachedHigh = $cachedHigh;

        return $this;
    }

    public function getCachedLow(): ?string
    {
        return $this->cachedLow;
    }

    public function setCachedLow(?string $cachedLow): static
    {
        $this->cachedLow = $cachedLow;

        return $this;
    }

    public function getQuoteCachedAt(): ?\DateTimeImmutable
    {
        return $this->quoteCachedAt;
    }

    public function setQuoteCachedAt(?\DateTimeImmutable $quoteCachedAt): static
    {
        $this->quoteCachedAt = $quoteCachedAt;

        return $this;
    }

    /**
     * @return Collection<int, WatchlistItem>
     */
    public function getStock(): Collection
    {
        return $this->stock;
    }

    public function addStock(WatchlistItem $stock): static
    {
        if (!$this->stock->contains($stock)) {
            $this->stock->add($stock);
            $stock->setStock($this);
        }

        return $this;
    }

    public function removeStock(WatchlistItem $stock): static
    {
        if ($this->stock->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getStock() === $this) {
                $stock->setStock(null);
            }
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
            $alert->setStock($this);
        }

        return $this;
    }

    public function removeAlert(Alert $alert): static
    {
        if ($this->alerts->removeElement($alert)) {
            // set the owning side to null (unless already changed)
            if ($alert->getStock() === $this) {
                $alert->setStock(null);
            }
        }

        return $this;
    }
}
