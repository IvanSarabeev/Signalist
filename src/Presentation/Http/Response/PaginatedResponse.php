<?php

namespace App\Presentation\Http\Response;

readonly class PaginatedResponse
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

    public function toArray(): array
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
}
