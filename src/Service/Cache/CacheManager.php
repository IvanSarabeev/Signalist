<?php

declare(strict_types=1);

namespace App\Service\Cache;

use Psr\Log\LoggerInterface;
use Random\RandomException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Throwable;

/**
 * Application cache facade.
 *
 * Responsibilities:
 * - apply the profile's TTL, cacheJitter and stampede settings consistently
 * - tag every entry so it can be invalidated by subject
 * - degrade gracefully: a broken cache backend must never break a request
 */
final readonly class CacheManager implements CacheManagerInterface
{
    private const LOG_PREFIX = 'Cache : ';

    public function __construct(
        #[Autowire(service: 'signalist.cache')]
        private TagAwareCacheInterface $cache,
        private CacheKeyFactory        $keyFactory,
        private LoggerInterface        $logger,
    )
    { }

    /**
     * @inheritDoc
     */
    public function get(
        CacheProfile $profile,
        array $parts,
        callable $callback,
        array $tags = [],
        bool $forceRefresh = false
    ): mixed
    {
        $key = $this->keyFactory->build($profile, $parts);

        try {
            return $this->cache->get(
                $key,
                function (ItemInterface $item) use ($profile, $callback, $tags): mixed {
                    $value = $callback();

                    $item->expiresAfter($this->lifetimeFor($profile, $value));

                    if ($tags !== []) {
                        $item->tag($tags);
                    }

                    return $value;
                },
                $forceRefresh ? INF : $profile->secureStampede(),
            );
        } catch (Throwable $throwable) {
            $this->logger->error(self::LOG_PREFIX . 'read failed, bypassing adapter.', [
                'key'       => $key,
                'exception' => $throwable,
            ]);

            return $callback();
        }
    }

    public function delete(CacheProfile $profile, array $parts = []): void
    {
        $key= $this->keyFactory->build($profile, $parts);

        try {
            $this->cache->delete($key);
        } catch (Throwable $throwable) {
            $this->logger->error(self::LOG_PREFIX . 'delete failed.', [
                'key'       => $key,
                'exception' => $throwable,
            ]);
        }
    }

    public function invalidate(string ...$tags): void
    {
        if ($tags === []) {
            return;
        }

        try {
            $this->cache->invalidateTags($tags);
        } catch (Throwable $throwable) {
            $this->logger->error(self::LOG_PREFIX . 'tag invalidation failed.', [
                'tags'      => $tags,
                'exception' => $throwable,
            ]);
        }
    }

    /**
     * Base TTL + uniform random spread, so entries written together do not expire together.
     *
     * Empty results get a deliberately short lifetime (negative caching) to stop
     * unknown symbols from passing straight through to Finnhub on every request.
     */
    private function lifetimeFor(CacheProfile $profile, mixed $value): int
    {
        if (empty($value)) {
            return $profile->emptyTtl();
        }

        $ttl = $profile->ttl();
        $spread = (int) round($ttl * $profile->cacheJitter());

        if ($spread <= 0) {
            return $ttl;
        }

        try {
            return $ttl + random_int(0, $spread);
        } catch (RandomException) {
            return $ttl;
        }
    }
}
