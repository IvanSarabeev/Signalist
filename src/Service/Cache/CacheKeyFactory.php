<?php

declare(strict_types=1);

namespace App\Service\Cache;

use Symfony\Contracts\Cache\ItemInterface;

final readonly class CacheKeyFactory
{
    private const SEPARATOR = '.';
    private const MAX_FRAGMENT_LENGTH = 48;

    /**
     * @param CacheProfile $profile
     * @param array<int, string|int|float|bool|null> $parts Ordered discriminators
     * @return string
     */
    public function build(CacheProfile $profile, array $parts = []): string
    {
        if ($parts === []) {
            return $profile->value;
        }

        $fragments = array_map(
            fn (string|int|float|bool|null $part): string => $this->normalizeValue($part),
            $parts
        );

        return $profile->value . self::SEPARATOR . implode(self::SEPARATOR, $fragments);
    }

    private function normalizeValue(string|int|float|bool|null $part): string
    {
        $value = match (true) {
            $part === null => 'null',
            is_bool($part) => $part ? 1 : 0,
            default        => (string) $part,
        };

        $value = str_replace(
            str_split(ItemInterface::RESERVED_CHARACTERS),
            '_',
            $value
        );

        if (mb_strlen($value) > self::MAX_FRAGMENT_LENGTH) {
            return substr(hash('xxh128', $value), 0, 16);
        }

        return $value;
    }
}
