<?php

namespace App\Presentation\Http\Request;

use Symfony\Component\HttpFoundation\Request;

readonly class PaginatedRequest
{
    public function __construct(
        public int $page  = 1,
        public int $limit = 10,
    )
    { }

    public static function fromRequest(Request $request): self
    {
        return new self(
            page: min(100, max(1, $request->query->getInt('page', 1))),
            limit: min(50, max(1, $request->query->getInt('limit', 10))),
        );
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
