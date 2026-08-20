<?php

declare(strict_types=1);

namespace App\Service\Cache;

interface CacheManagerInterface
{
    /**
     * @template T
     * @param array<int, string|int|float|bool|null> $parts
     * @param callable(): T                          $callback
     * @param array<int, string>                     $tags
     * @return T
     */
    public function get(
        CacheProfile $profile,
        array $parts,
        callable $callback,
        array $tags = [],
        bool $forceRefresh = false,
    ): mixed;

    public function delete(CacheProfile $profile, array $parts = []): void;

    public function invalidate(array|string $tags): void;
}
