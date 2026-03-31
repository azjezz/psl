<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Cache\Exception\UnavailableItemException;
use Psl\Cache\StoreInterface;
use Psl\DateTime\Duration;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\Record\DNSKEYRecord;
use Psl\DNSSEC\CachedTrustChainResolver;
use Psl\DNSSEC\ChainFailure;
use Psl\DNSSEC\TrustChainResolverInterface;
use Psl\DNSSEC\TrustChainResult;
use Psl\DNSSEC\TrustChainStatus;

final class CachedTrustChainResolverTest extends TestCase
{
    public function testCacheHitReturnsCachedResultWithoutCallingInner(): void
    {
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, 'key-data');
        $cached = new TrustChainResult(TrustChainStatus::Secure, [$dnskey]);

        $innerCallCount = 0;
        $inner = new class($innerCallCount) implements TrustChainResolverInterface {
            private int $count;

            public function __construct(int &$count)
            {
                $this->count = &$count;
            }

            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                $this->count++;
                return new TrustChainResult(TrustChainStatus::Secure, []);
            }
        };

        $store = self::createCacheStore(['dnssec:example.com' => $cached]);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $result = $resolver->resolve('example.com');

        static::assertSame(TrustChainStatus::Secure, $result->status);
        static::assertCount(1, $result->keys);
        static::assertSame(0, $innerCallCount);
    }

    public function testCacheMissCallsInnerResolverAndCachesResult(): void
    {
        $dnskey = new DNSKEYRecord('example.com', Duration::seconds(300), 257, 3, Algorithm::RSASHA256, 'key-data');
        $innerResult = new TrustChainResult(TrustChainStatus::Secure, [$dnskey]);

        $innerCallCount = 0;
        $inner = new class($innerCallCount, $innerResult) implements TrustChainResolverInterface {
            private int $count;

            public function __construct(
                int &$count,
                private readonly TrustChainResult $result,
            ) {
                $this->count = &$count;
            }

            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                $this->count++;
                return $this->result;
            }
        };

        $store = self::createCacheStore([]);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $result = $resolver->resolve('example.com');

        static::assertSame(TrustChainStatus::Secure, $result->status);
        static::assertCount(1, $result->keys);
        static::assertSame(1, $innerCallCount);
    }

    public function testDifferentZonesGetDifferentCacheEntries(): void
    {
        $dnskey1 = new DNSKEYRecord('com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, 'key-com');
        $dnskey2 = new DNSKEYRecord('net', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, 'key-net');

        $resolvedZones = [];
        $inner = new class($resolvedZones, $dnskey1, $dnskey2) implements TrustChainResolverInterface {
            /** @var list<string> */
            private array $zones;

            public function __construct(
                array &$zones,
                private readonly DNSKEYRecord $key1,
                private readonly DNSKEYRecord $key2,
            ) {
                $this->zones = &$zones;
            }

            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                $this->zones[] = $zone;
                if ($zone === 'com') {
                    return new TrustChainResult(TrustChainStatus::Secure, [$this->key1]);
                }

                return new TrustChainResult(TrustChainStatus::Secure, [$this->key2]);
            }
        };

        $store = self::createCacheStore([]);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $result1 = $resolver->resolve('com');
        $result2 = $resolver->resolve('net');

        static::assertSame(TrustChainStatus::Secure, $result1->status);
        static::assertSame(TrustChainStatus::Secure, $result2->status);
        static::assertSame('key-com', $result1->keys[0]->publicKey);
        static::assertSame('key-net', $result2->keys[0]->publicKey);
        static::assertContains('com', $resolvedZones);
        static::assertContains('net', $resolvedZones);
    }

    public function testInsecureResultIsCached(): void
    {
        $innerCallCount = 0;
        $inner = new class($innerCallCount) implements TrustChainResolverInterface {
            private int $count;

            public function __construct(int &$count)
            {
                $this->count = &$count;
            }

            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                $this->count++;
                return new TrustChainResult(TrustChainStatus::Insecure, []);
            }
        };

        $store = self::createCacheStore([]);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $result = $resolver->resolve('unsigned.example.com');

        static::assertSame(TrustChainStatus::Insecure, $result->status);
        static::assertSame(1, $innerCallCount);
    }

    public function testBogusResultIsCached(): void
    {
        $innerCallCount = 0;
        $inner = new class($innerCallCount) implements TrustChainResolverInterface {
            private int $count;

            public function __construct(int &$count)
            {
                $this->count = &$count;
            }

            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                $this->count++;
                return new TrustChainResult(TrustChainStatus::Bogus, [], ChainFailure::MissingDnskey);
            }
        };

        $store = self::createCacheStore([]);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $result = $resolver->resolve('broken.example.com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertSame(ChainFailure::MissingDnskey, $result->failure);
        static::assertSame(1, $innerCallCount);
    }

    public function testCacheKeyIsLowercased(): void
    {
        $computedKeys = [];

        $inner = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Insecure, []);
            }
        };

        $store = self::createTrackingCacheStore($computedKeys);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $resolver->resolve('EXAMPLE.COM');

        static::assertContains('dnssec:example.com', $computedKeys);
    }

    public function testSecureResultWithNoKeysDoesNotSetTtl(): void
    {
        $inner = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Secure, []);
            }
        };

        $updateCalls = [];
        $store = self::createTrackingUpdateStore($updateCalls);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $result = $resolver->resolve('example.com');

        static::assertSame(TrustChainStatus::Secure, $result->status);
        static::assertSame([], $updateCalls);
    }

    public function testSecureResultWithKeysSetsTtlToMinKeyTtl(): void
    {
        $dnskey1 = new DNSKEYRecord('example.com', Duration::seconds(7200), 257, 3, Algorithm::RSASHA256, 'key1');
        $dnskey2 = new DNSKEYRecord('example.com', Duration::seconds(3600), 257, 3, Algorithm::RSASHA256, 'key2');
        $innerResult = new TrustChainResult(TrustChainStatus::Secure, [$dnskey1, $dnskey2]);

        $inner = new class($innerResult) implements TrustChainResolverInterface {
            public function __construct(
                private readonly TrustChainResult $result,
            ) {}

            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return $this->result;
            }
        };

        $updateCalls = [];
        $store = self::createTrackingUpdateStore($updateCalls);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $result = $resolver->resolve('example.com');

        static::assertSame(TrustChainStatus::Secure, $result->status);
        static::assertCount(1, $updateCalls);
        static::assertSame(3600, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testInsecureResultSetsTtlToInsecureCacheTtl(): void
    {
        $inner = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Insecure, []);
            }
        };

        $updateCalls = [];
        $store = self::createTrackingUpdateStore($updateCalls);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $result = $resolver->resolve('unsigned.example.com');

        static::assertSame(TrustChainStatus::Insecure, $result->status);
        static::assertCount(1, $updateCalls);
        static::assertSame(300, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testBogusResultSetsTtlToBogusCacheTtl(): void
    {
        $inner = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Bogus, [], ChainFailure::MissingDnskey);
            }
        };

        $updateCalls = [];
        $store = self::createTrackingUpdateStore($updateCalls);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $result = $resolver->resolve('broken.example.com');

        static::assertSame(TrustChainStatus::Bogus, $result->status);
        static::assertCount(1, $updateCalls);
        static::assertSame(30, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testSecureResultWithZeroTtlKeysDoesNotSetTtl(): void
    {
        $dnskey = new DNSKEYRecord('example.com', Duration::zero(), 257, 3, Algorithm::RSASHA256, 'key');
        $innerResult = new TrustChainResult(TrustChainStatus::Secure, [$dnskey]);

        $inner = new class($innerResult) implements TrustChainResolverInterface {
            public function __construct(
                private readonly TrustChainResult $result,
            ) {}

            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return $this->result;
            }
        };

        $updateCalls = [];
        $store = self::createTrackingUpdateStore($updateCalls);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $result = $resolver->resolve('example.com');

        static::assertSame(TrustChainStatus::Secure, $result->status);
        static::assertSame([], $updateCalls);
    }

    public function testInsecureTtlDiffersFromBogusTtl(): void
    {
        $innerCallIndex = 0;
        $inner = new class($innerCallIndex) implements TrustChainResolverInterface {
            private int $index;

            public function __construct(int &$index)
            {
                $this->index = &$index;
            }

            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                $this->index++;
                if ($this->index === 1) {
                    return new TrustChainResult(TrustChainStatus::Insecure, []);
                }

                return new TrustChainResult(TrustChainStatus::Bogus, [], ChainFailure::MissingDnskey);
            }
        };

        $updateCalls = [];
        $store = self::createTrackingUpdateStore($updateCalls);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $resolver->resolve('insecure.com');
        $resolver->resolve('bogus.com');

        static::assertCount(2, $updateCalls);
        $insecureTtl = (int) $updateCalls[0]['ttl']->getTotalSeconds();
        $bogusTtl = (int) $updateCalls[1]['ttl']->getTotalSeconds();

        static::assertSame(300, $insecureTtl);
        static::assertSame(30, $bogusTtl);
        static::assertNotSame($insecureTtl, $bogusTtl);
    }

    public function testComputeMaxTtlIsExactly24Hours(): void
    {
        $computeTtls = [];

        $inner = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Secure, []);
            }
        };

        $store = self::createComputeTtlTrackingStore($computeTtls);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $resolver->resolve('example.com');

        static::assertCount(1, $computeTtls);
        static::assertNotNull($computeTtls[0]);
        static::assertSame(24 * 3600, (int) $computeTtls[0]->getTotalSeconds());
    }

    public function testComputeMaxTtlIsNot23Hours(): void
    {
        $computeTtls = [];

        $inner = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Insecure, []);
            }
        };

        $store = self::createComputeTtlTrackingStore($computeTtls);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $resolver->resolve('example.com');

        static::assertCount(1, $computeTtls);
        static::assertNotNull($computeTtls[0]);
        static::assertNotSame(23 * 3600, (int) $computeTtls[0]->getTotalSeconds());
    }

    public function testComputeMaxTtlIsNot25Hours(): void
    {
        $computeTtls = [];

        $inner = new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Bogus, [], ChainFailure::MissingDnskey);
            }
        };

        $store = self::createComputeTtlTrackingStore($computeTtls);
        $resolver = new CachedTrustChainResolver($inner, $store);

        $resolver->resolve('broken.com');

        static::assertCount(1, $computeTtls);
        static::assertNotNull($computeTtls[0]);
        static::assertNotSame(25 * 3600, (int) $computeTtls[0]->getTotalSeconds());
    }

    /**
     * @param list<Duration|null> $computeTtls
     */
    private static function createComputeTtlTrackingStore(array &$computeTtls): StoreInterface
    {
        return new class($computeTtls) implements StoreInterface {
            /** @var list<Duration|null> */
            private array $ttls;

            /** @param list<Duration|null> $ttls */
            public function __construct(array &$ttls)
            {
                $this->ttls = &$ttls;
            }

            public function get(string $key): mixed
            {
                throw new UnavailableItemException($key);
            }

            public function compute(string $key, Closure $computer, null|Duration $ttl = null): mixed
            {
                $this->ttls[] = $ttl;
                return $computer();
            }

            public function update(string $key, Closure $computer, null|Duration $ttl = null): mixed
            {
                return $computer(null);
            }

            public function delete(string $key): void {}
        };
    }

    /**
     * @param array<string, TrustChainResult> $preloaded
     */
    private static function createCacheStore(array $preloaded): StoreInterface
    {
        return new class($preloaded) implements StoreInterface {
            /** @param array<string, TrustChainResult> $data */
            public function __construct(
                private array $data,
            ) {}

            public function get(string $key): mixed
            {
                if (isset($this->data[$key])) {
                    return $this->data[$key];
                }

                throw new UnavailableItemException($key);
            }

            public function compute(string $key, Closure $computer, null|Duration $ttl = null): mixed
            {
                if (isset($this->data[$key])) {
                    return $this->data[$key];
                }

                $value = $computer();
                $this->data[$key] = $value;
                return $value;
            }

            public function update(string $key, Closure $computer, null|Duration $ttl = null): mixed
            {
                $value = $computer(null);
                $this->data[$key] = $value;
                return $value;
            }

            public function delete(string $key): void
            {
                unset($this->data[$key]);
            }
        };
    }

    /**
     * @param list<string> $computedKeys
     */
    private static function createTrackingCacheStore(array &$computedKeys): StoreInterface
    {
        return new class($computedKeys) implements StoreInterface {
            /** @var list<string> */
            private array $keys;

            /** @param list<string> $keys */
            public function __construct(array &$keys)
            {
                $this->keys = &$keys;
            }

            public function get(string $key): mixed
            {
                throw new UnavailableItemException($key);
            }

            public function compute(string $key, Closure $computer, null|Duration $ttl = null): mixed
            {
                $this->keys[] = $key;
                return $computer();
            }

            public function update(string $key, Closure $computer, null|Duration $ttl = null): mixed
            {
                return $computer(null);
            }

            public function delete(string $key): void {}
        };
    }

    /**
     * @param list<array{key: string, ttl: Duration|null}> $updateCalls
     */
    private static function createTrackingUpdateStore(array &$updateCalls): StoreInterface
    {
        return new class($updateCalls) implements StoreInterface {
            /** @var list<array{key: string, ttl: Duration|null}> */
            private array $calls;

            /** @param list<array{key: string, ttl: Duration|null}> $calls */
            public function __construct(array &$calls)
            {
                $this->calls = &$calls;
            }

            public function get(string $key): mixed
            {
                throw new UnavailableItemException($key);
            }

            public function compute(string $key, Closure $computer, null|Duration $ttl = null): mixed
            {
                return $computer();
            }

            public function update(string $key, Closure $computer, null|Duration $ttl = null): mixed
            {
                $this->calls[] = ['key' => $key, 'ttl' => $ttl];
                return $computer(null);
            }

            public function delete(string $key): void {}
        };
    }
}
