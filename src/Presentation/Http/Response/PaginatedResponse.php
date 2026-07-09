<?php

namespace App\Presentation\Http\Response;

final readonly class PaginatedResponse
{
    public bool $hasNextPage;
    public bool $hasPreviousPage;

    public function __construct(
        public array $items,
        public int   $total,
        public int   $page,
        public int   $limit,
        public int   $total_pages,
    )
    {
        $this->hasNextPage = $this->page < $this->total_pages;
        $this->hasPreviousPage = $this->page > 1;
    }

    public static function fromArray(array $items, int $page, int $limit): self
    {
        $total      = count($items);
        $totalPages = $limit > 0 ? (int) ceil($total / $limit) : 0;
        $offset     = ($page - 1) * $limit;

        return new self(
            items:       array_slice($items, $offset, $limit),
            total:       $total,
            page:        $page,
            limit:       $limit,
            total_pages: $totalPages,
        );
    }

    public function meta(): array
    {
        return [
            'total'             => $this->total,
            'page'              => $this->page,
            'limit'             => $this->limit,
            'total_pages'       => $this->total_pages,
            'has_next_page'     => $this->hasNextPage,
            'has_previous_page' => $this->hasPreviousPage,
        ];
    }

    public function toArray(): array
    {
        return [
            'items' => $this->items,
            ...$this->meta(),
        ];
    }
}
