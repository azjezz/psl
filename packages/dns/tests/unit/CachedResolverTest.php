<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Cache\LocalStore;
use Psl\DateTime\Duration;
use Psl\DNS\CachedResolver;
use Psl\DNS\EDNS;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\NSRecord;
use Psl\DNS\Record\PTRRecord;
use Psl\DNS\Record\RecordType;
use Psl\DNS\ResolverInterface;
use Psl\DNS\Response;
use Psl\DNS\ResponseCode;
use Psl\DNS\StaticResolver;
use Psl\IP\Address;

final class CachedResolverTest extends TestCase
{
    public function testCachesSuccessfulResponse(): void
    {
        $inner = new StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::seconds(300), Address::v4('10.0.0.1')),
                ],
            ],
        ]);

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $response1 = $resolver->query('example.com', RecordType::A);
        static::assertSame(ResponseCode::NoError, $response1->code);
        static::assertCount(1, $response1->answers);
        $response2 = $resolver->query('example.com', RecordType::A);
        static::assertSame(ResponseCode::NoError, $response2->code);
        static::assertCount(1, $response2->answers);
    }

    public function testDelegatesToInnerOnCacheMiss(): void
    {
        $inner = new StaticResolver([]);

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $response = $resolver->query('unknown.example.com', RecordType::A);
        static::assertSame(ResponseCode::NonExistentDomain, $response->code);
    }

    public function testDifferentQueriesGetDifferentCacheEntries(): void
    {
        $inner = new StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::seconds(300), Address::v4('10.0.0.1')),
                ],
            ],
        ]);

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $responseA = $resolver->query('example.com', RecordType::A);
        static::assertSame(ResponseCode::NoError, $responseA->code);
        static::assertCount(1, $responseA->answers);

        $responseAAAA = $resolver->query('example.com', RecordType::AAAA);
        static::assertSame(ResponseCode::NoError, $responseAAAA->code);
        static::assertCount(0, $responseAAAA->answers);
    }

    public function testReverseQueryDelegatesToQuery(): void
    {
        $ip = Address::v4('10.0.0.1');
        $inner = new StaticResolver([
            $ip->toArpaName() => [
                RecordType::PTR->value => [
                    new PTRRecord($ip->toArpaName(), Duration::seconds(300), 'example.com'),
                ],
            ],
        ]);

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $response = $resolver->reverseQuery($ip);
        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertCount(1, $response->answers);
    }

    public function testServerFailureResponseIsNotCachedWithTtl(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(0, ResponseCode::ServerFailure, [], [], []);
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('fail.example.com', RecordType::A);
        $resolver->query('fail.example.com', RecordType::A);

        static::assertSame(1, $callCount);
    }

    public function testNoErrorResponseIsCachedWithTtl(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::seconds(300), Address::v4('10.0.0.1')),
                ],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $r1 = $resolver->query('example.com', RecordType::A);
        $r2 = $resolver->query('example.com', RecordType::A);

        static::assertSame(1, $callCount);
        static::assertSame(ResponseCode::NoError, $r1->code);
        static::assertSame(ResponseCode::NoError, $r2->code);
    }

    public function testNonExistentDomainResponseIsCachedWithTtl(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NonExistentDomain,
                [],
                [
                    new NSRecord('example.com', Duration::seconds(120), 'ns1.example.com'),
                ],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $r1 = $resolver->query('nx.example.com', RecordType::A);
        $r2 = $resolver->query('nx.example.com', RecordType::A);

        static::assertSame(1, $callCount);
        static::assertSame(ResponseCode::NonExistentDomain, $r1->code);
        static::assertSame(ResponseCode::NonExistentDomain, $r2->code);
    }

    public function testZeroTtlRecordDoesNotTriggerCacheUpdate(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::zero(), Address::v4('10.0.0.1')),
                ],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('example.com', RecordType::A);

        static::assertSame(1, $callCount);
    }

    public function testBuildKeyCaseInsensitive(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::seconds(300), Address::v4('10.0.0.1')),
                ],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('Example.COM', RecordType::A);
        $resolver->query('example.com', RecordType::A);
        $resolver->query('EXAMPLE.COM', RecordType::A);

        static::assertSame(1, $callCount, 'All case variants should hit the same cache key');
    }

    public function testBuildKeyIncludesRecordType(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::seconds(300), Address::v4('10.0.0.1')),
                ],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('example.com', RecordType::A);
        $resolver->query('example.com', RecordType::AAAA);

        static::assertSame(2, $callCount, 'Different record types should be different cache keys');
    }

    public function testEdnsOptionsAffectCacheKey(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::seconds(300), Address::v4('10.0.0.1')),
                ],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('example.com', RecordType::A);
        $resolver->query('example.com', RecordType::A, ednsOptions: [new EDNS\NSIDOption()]);

        static::assertSame(2, $callCount, 'EDNS options should create a different cache key');
    }

    public function testExtractMinTtlUsesAuthorityWhenNoAnswers(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NonExistentDomain,
                [],
                [
                    new NSRecord('example.com', Duration::seconds(60), 'ns1.example.com'),
                ],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $r1 = $resolver->query('nx.example.com', RecordType::A);
        static::assertSame(ResponseCode::NonExistentDomain, $r1->code);
        static::assertSame(1, $callCount);
    }

    public function testExtractMinTtlUsesAnswersOverAuthority(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::seconds(200), Address::v4('10.0.0.1')),
                ],
                [
                    new NSRecord('example.com', Duration::seconds(10), 'ns1.example.com'),
                ],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $r1 = $resolver->query('example.com', RecordType::A);
        static::assertSame(ResponseCode::NoError, $r1->code);
        static::assertCount(1, $r1->answers);
    }

    public function testNoErrorResponseWithPositiveTtlUpdatesCacheEntry(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::seconds(120), Address::v4('10.0.0.1')),
                ],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('example.com', RecordType::A);
        $resolver->query('example.com', RecordType::A);

        static::assertSame(1, $callCount);
    }

    public function testServerFailureIsNotCachedWithRecordTtl(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(0, ResponseCode::ServerFailure, [], [], []);
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('fail.example.com', RecordType::A);

        static::assertSame(1, $callCount);
    }

    public function testExtractMinTtlReturns60WhenNoRecords(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(0, ResponseCode::NoError, [], [], []);
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('empty.example.com', RecordType::A);

        static::assertSame(1, $callCount);
    }

    public function testExtractMinTtlPrefersAnswersOverAuthority(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::seconds(100), Address::v4('10.0.0.1')),
                ],
                [
                    new NSRecord('example.com', Duration::seconds(5), 'ns1.example.com'),
                ],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $r1 = $resolver->query('example.com', RecordType::A);
        $r2 = $resolver->query('example.com', RecordType::A);

        static::assertSame(1, $callCount);
        static::assertCount(1, $r1->answers);
        static::assertCount(1, $r2->answers);
    }

    public function testCacheKeyIsSeparatedByColon(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('a1.example.com', Duration::seconds(300), Address::v4('10.0.0.1')),
                ],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('a1.example.com', RecordType::A);
        $resolver->query('a1.example.com', RecordType::A);

        static::assertSame(1, $callCount);
    }

    public function testBuildKeyEdnsOptionsAppendHashWithSeparator(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::seconds(300), Address::v4('10.0.0.1')),
                ],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $option = new EDNS\NSIDOption();
        $resolver->query('example.com', RecordType::A, ednsOptions: [$option]);
        $resolver->query('example.com', RecordType::A, ednsOptions: [$option]);

        static::assertSame(1, $callCount);
    }

    public function testNoEdnsOptionsDoesNotAppendHash(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::seconds(300), Address::v4('10.0.0.1')),
                ],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('example.com', RecordType::A);

        $resolver->query('example.com', RecordType::A, ednsOptions: [new EDNS\NSIDOption()]);

        static::assertSame(2, $callCount);
    }

    public function testNonExistentDomainWithAuthorityRecordsCaches(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NonExistentDomain,
                [],
                [
                    new NSRecord('example.com', Duration::seconds(120), 'ns1.example.com'),
                    new NSRecord('example.com', Duration::seconds(60), 'ns2.example.com'),
                ],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $r1 = $resolver->query('nx.example.com', RecordType::A);
        $r2 = $resolver->query('nx.example.com', RecordType::A);

        static::assertSame(1, $callCount);
        static::assertSame(ResponseCode::NonExistentDomain, $r1->code);
        static::assertSame(ResponseCode::NonExistentDomain, $r2->code);
    }

    public function testNoErrorCachedButNotServerFailure(): void
    {
        $callCountNoError = 0;
        $innerNoError = self::countingResolver(static function () use (&$callCountNoError): Response {
            $callCountNoError++;
            return new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('ok.example.com', Duration::seconds(300), Address::v4('10.0.0.1'))],
                [],
                [],
            );
        });

        $callCountFail = 0;
        $innerFail = self::countingResolver(static function () use (&$callCountFail): Response {
            $callCountFail++;
            return new Response(0, ResponseCode::ServerFailure, [], [], []);
        });

        $cacheOk = new LocalStore();
        $resolverOk = new CachedResolver($innerNoError, $cacheOk);
        $resolverOk->query('ok.example.com', RecordType::A);
        $resolverOk->query('ok.example.com', RecordType::A);
        static::assertSame(1, $callCountNoError);

        $cacheFail = new LocalStore();
        $resolverFail = new CachedResolver($innerFail, $cacheFail);
        $resolverFail->query('fail.example.com', RecordType::A);
        static::assertSame(1, $callCountFail);
    }

    public function testDifferentDomainsAreDifferentCacheEntries(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('test.com', Duration::seconds(300), Address::v4('10.0.0.1'))],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('alpha.example.com', RecordType::A);
        $resolver->query('beta.example.com', RecordType::A);

        static::assertSame(2, $callCount);
    }

    public function testSameDomainDifferentTypesAreDifferentEntries(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('example.com', Duration::seconds(300), Address::v4('10.0.0.1'))],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('example.com', RecordType::A);
        $resolver->query('example.com', RecordType::MX);

        static::assertSame(2, $callCount);
    }

    public function testNxdomainWithNoRecordsStillCaches(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(0, ResponseCode::NonExistentDomain, [], [], []);
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $r1 = $resolver->query('nx.example.com', RecordType::A);
        $r2 = $resolver->query('nx.example.com', RecordType::A);

        static::assertSame(1, $callCount);
        static::assertSame(ResponseCode::NonExistentDomain, $r1->code);
        static::assertSame(ResponseCode::NonExistentDomain, $r2->code);
    }

    public function testOnlyNoErrorAndNxdomainTriggerTtlUpdate(): void
    {
        $serverFailCallCount = 0;
        $innerFail = self::countingResolver(static function () use (&$serverFailCallCount): Response {
            $serverFailCallCount++;
            return new Response(0, ResponseCode::ServerFailure, [], [], []);
        });

        $cacheFail = new LocalStore();
        $resolverFail = new CachedResolver($innerFail, $cacheFail);
        $resolverFail->query('sf.example.com', RecordType::A);
        static::assertSame(1, $serverFailCallCount);

        $noErrorCallCount = 0;
        $innerNoError = self::countingResolver(static function () use (&$noErrorCallCount): Response {
            $noErrorCallCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('ok.example.com', Duration::seconds(300), Address::v4('10.0.0.1'))],
                [],
                [],
            );
        });

        $cacheOk = new LocalStore();
        $resolverOk = new CachedResolver($innerNoError, $cacheOk);
        $resolverOk->query('ok.example.com', RecordType::A);
        $resolverOk->query('ok.example.com', RecordType::A);
        static::assertSame(1, $noErrorCallCount);
    }

    public function testAuthorityRecordsUsedForNxdomainTtl(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NonExistentDomain,
                [],
                [new NSRecord('example.com', Duration::seconds(300), 'ns1.example.com')],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $r1 = $resolver->query('nx.example.com', RecordType::A);
        $r2 = $resolver->query('nx.example.com', RecordType::A);

        static::assertSame(1, $callCount);
        static::assertSame(ResponseCode::NonExistentDomain, $r1->code);
        static::assertSame(ResponseCode::NonExistentDomain, $r2->code);
        static::assertCount(1, $r1->authority);
    }

    /**
     * Helper: creates a resolver that counts calls and delegates to a callback.
     *
     * @param \Closure(): Response $handler
     */
    private static function countingResolver(\Closure $handler): object
    {
        return new class($handler) implements ResolverInterface {
            /** @var \Closure(): Response */
            private \Closure $handler;

            public function __construct(\Closure $handler)
            {
                $this->handler = $handler;
            }

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return ($this->handler)();
            }

            public function reverseQuery(
                Address $ip,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return ($this->handler)();
            }
        };
    }
}
