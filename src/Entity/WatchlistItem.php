<?php

namespace App\Entity;

use App\Repository\WatchlistItemRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WatchlistItemRepository::class)]
#[ORM\UniqueConstraint(columns: ['user_id', 'stock_symbol'])]
class WatchlistItem
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'watchlistItems')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Stock::class, inversedBy: 'watchlistItems')]
    #[ORM\JoinColumn(name: 'stock_symbol', referencedColumnName: 'symbol', nullable: false)]
    private Stock $stock;

    #[ORM\Column(type: Types::INTEGER)]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $addedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStock(): Stock
    {
        return $this->stock;
    }

    public function setStock(Stock $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getAddedAt(): DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'stock' => $this->getStock(),
            'order' => $this->getSortOrder(),
            'addedAt' => $this->getAddedAt()->format('Y-m-d\TH:i:s'),
        ];
    }
}
