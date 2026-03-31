<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Cache\Exception\UnavailableItemException;
use Psl\Cache\LocalStore;
use Psl\Cache\StoreInterface;
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

use function md5;
use function serialize;

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

    public function testExtractMinTtlDefaultIs60WhenNoRecords(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(static fn(): Response => new Response(0, ResponseCode::NoError, [], [], []));

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('empty.example.com', RecordType::A);

        static::assertCount(1, $updateCalls);
        static::assertSame(60, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testExtractMinTtlDefaultValueIsNotLessThan60(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(static fn(): Response => new Response(0, ResponseCode::NoError, [], [], []));

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('empty.example.com', RecordType::A);

        static::assertCount(1, $updateCalls);
        static::assertGreaterThanOrEqual(60, (int) $updateCalls[0]['ttl']->getTotalSeconds());
        static::assertLessThanOrEqual(60, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testExtractMinTtlDefaultValueIsNotGreaterThan60(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(0, ResponseCode::NonExistentDomain, [], [], []),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('nx.example.com', RecordType::A);

        static::assertCount(1, $updateCalls);
        static::assertSame(60, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testAuthorityTtlIsUsedWhenNoAnswersPresent(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NonExistentDomain,
                [],
                [
                    new NSRecord('example.com', Duration::seconds(45), 'ns1.example.com'),
                    new NSRecord('example.com', Duration::seconds(90), 'ns2.example.com'),
                ],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('nx.example.com', RecordType::A);

        static::assertCount(1, $updateCalls);
        static::assertSame(45, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testAuthorityTtlSetsHasRecordsFlagCorrectly(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NonExistentDomain,
                [],
                [
                    new NSRecord('example.com', Duration::seconds(200), 'ns1.example.com'),
                ],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('nx.example.com', RecordType::A);

        static::assertCount(1, $updateCalls);
        static::assertSame(200, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testAuthorityNotUsedWhenAnswersExist(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::seconds(100), Address::v4('10.0.0.1')),
                ],
                [
                    new NSRecord('example.com', Duration::seconds(5), 'ns1.example.com'),
                ],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('example.com', RecordType::A);

        static::assertCount(1, $updateCalls);
        static::assertSame(100, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testMultipleAuthorityRecordsUsesMinimumTtl(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NonExistentDomain,
                [],
                [
                    new NSRecord('example.com', Duration::seconds(500), 'ns1.example.com'),
                    new NSRecord('example.com', Duration::seconds(120), 'ns2.example.com'),
                    new NSRecord('example.com', Duration::seconds(300), 'ns3.example.com'),
                ],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('nx.example.com', RecordType::A);

        static::assertCount(1, $updateCalls);
        static::assertSame(120, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testNoErrorResponseTriggersTtlExtraction(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('ok.example.com', Duration::seconds(60), Address::v4('10.0.0.1'))],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('ok.example.com', RecordType::A);
        $resolver->query('ok.example.com', RecordType::A);
        $resolver->query('ok.example.com', RecordType::A);

        static::assertSame(1, $callCount);
    }

    public function testNxdomainResponseTriggersTtlExtraction(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NonExistentDomain,
                [],
                [new NSRecord('example.com', Duration::seconds(60), 'ns1.example.com')],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('nx2.example.com', RecordType::A);
        $resolver->query('nx2.example.com', RecordType::A);
        $resolver->query('nx2.example.com', RecordType::A);

        static::assertSame(1, $callCount);
    }

    public function testServerFailureDoesNotTriggerTtlUpdate(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(0, ResponseCode::ServerFailure, [], [], []),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('sf.example.com', RecordType::A);

        static::assertCount(0, $updateCalls);
    }

    public function testFormatErrorDoesNotTriggerTtlUpdate(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(0, ResponseCode::FormatError, [], [], []),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('ferr.example.com', RecordType::A);

        static::assertCount(0, $updateCalls);
    }

    public function testRefusedDoesNotTriggerTtlUpdate(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(0, ResponseCode::ServerRefused, [], [], []),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('refused.example.com', RecordType::A);

        static::assertCount(0, $updateCalls);
    }

    public function testNoErrorTriggersUpdateButServerFailureDoesNot(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('ok.test', Duration::seconds(300), Address::v4('10.0.0.1'))],
                [],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('ok.test', RecordType::A);

        static::assertCount(1, $updateCalls);
    }

    public function testNxdomainTriggersUpdateWithAuthorityTtl(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NonExistentDomain,
                [],
                [new NSRecord('example.com', Duration::seconds(180), 'ns1.example.com')],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('nx.test', RecordType::A);

        static::assertCount(1, $updateCalls);
        static::assertSame(180, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testZeroTtlAnswerRecordDoesNotTriggerUpdate(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('zero.example.com', Duration::zero(), Address::v4('10.0.0.1'))],
                [],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('zero.example.com', RecordType::A);

        static::assertCount(0, $updateCalls);
    }

    public function testZeroTtlAuthorityRecordDoesNotTriggerUpdate(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NonExistentDomain,
                [],
                [new NSRecord('example.com', Duration::zero(), 'ns1.example.com')],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('zero-auth.example.com', RecordType::A);

        static::assertCount(0, $updateCalls);
    }

    public function testPositiveTtlCachesResponseForSubsequentCalls(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('cached.example.com', Duration::seconds(600), Address::v4('10.0.0.2'))],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $r1 = $resolver->query('cached.example.com', RecordType::A);
        $r2 = $resolver->query('cached.example.com', RecordType::A);
        $r3 = $resolver->query('cached.example.com', RecordType::A);

        static::assertSame(1, $callCount);
        static::assertSame($r1->code, $r2->code);
        static::assertSame($r1->code, $r3->code);
        static::assertCount(1, $r1->answers);
        static::assertCount(1, $r2->answers);
        static::assertCount(1, $r3->answers);
    }

    public function testCacheKeyColonSeparatorPreventsAmbiguity(): void
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

        $resolver->query('abc', RecordType::A);
        $resolver->query('abc', RecordType::AAAA);
        $resolver->query('abc', RecordType::MX);

        static::assertSame(3, $callCount);
    }

    public function testEdnsOptionsAppendSeparatorAndHashToKey(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('edns.example.com', Duration::seconds(300), Address::v4('10.0.0.1'))],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('edns.example.com', RecordType::A, ednsOptions: []);
        static::assertSame(1, $callCount);

        $resolver->query('edns.example.com', RecordType::A, ednsOptions: [new EDNS\NSIDOption()]);
        static::assertSame(2, $callCount);

        $resolver->query('edns.example.com', RecordType::A, ednsOptions: [new EDNS\NSIDOption()]);
        static::assertSame(2, $callCount);
    }

    public function testEmptyEdnsOptionsDoNotModifyKey(): void
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

        $resolver->query('example.com', RecordType::A, ednsOptions: []);
        $resolver->query('example.com', RecordType::A);

        static::assertSame(1, $callCount);
    }

    public function testDifferentEdnsOptionsCreateDifferentKeys(): void
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

        $resolver->query('example.com', RecordType::A, ednsOptions: [new EDNS\NSIDOption()]);
        $resolver->query('example.com', RecordType::A, ednsOptions: [new EDNS\NSIDOption('server1')]);

        static::assertSame(2, $callCount);
    }

    public function testExtractMinTtlAnswerRecordsPresentDoNotFallToAuthority(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NoError,
                [
                    new ARecord('example.com', Duration::seconds(100), Address::v4('10.0.0.1')),
                    new ARecord('example.com', Duration::seconds(200), Address::v4('10.0.0.2')),
                ],
                [
                    new NSRecord('example.com', Duration::seconds(5), 'ns1.example.com'),
                ],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('example.com', RecordType::A);

        static::assertCount(1, $updateCalls);
        static::assertSame(100, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testComputeCalledWith24HourTtl(): void
    {
        $computeTtls = [];
        $store = self::createComputeTrackingCacheStore($computeTtls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('example.com', Duration::seconds(300), Address::v4('10.0.0.1'))],
                [],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('example.com', RecordType::A);

        static::assertCount(1, $computeTtls);
        static::assertNotNull($computeTtls[0]);
        static::assertSame(86_400, (int) $computeTtls[0]->getTotalSeconds());
    }

    public function testComputeFallbackTtlIsExactly24Hours(): void
    {
        $computeTtls = [];
        $store = self::createComputeTrackingCacheStore($computeTtls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(0, ResponseCode::ServerFailure, [], [], []),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('sf.example.com', RecordType::A);

        static::assertCount(1, $computeTtls);
        static::assertNotNull($computeTtls[0]);
        static::assertSame(24 * 3600, (int) $computeTtls[0]->getTotalSeconds());
    }

    public function testComputeTtlIs24HoursNotMoreOrLess(): void
    {
        $computeTtls = [];
        $store = self::createComputeTrackingCacheStore($computeTtls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('example.com', Duration::seconds(120), Address::v4('10.0.0.1'))],
                [],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('example.com', RecordType::A);

        static::assertCount(1, $computeTtls);
        static::assertNotNull($computeTtls[0]);
        static::assertGreaterThanOrEqual(86_400, (int) $computeTtls[0]->getTotalSeconds());
        static::assertLessThanOrEqual(86_400, (int) $computeTtls[0]->getTotalSeconds());
    }

    public function testUpdateIsCalledWhenTtlValueIsNotNull(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('example.com', Duration::seconds(120), Address::v4('10.0.0.1'))],
                [],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('example.com', RecordType::A);

        static::assertCount(1, $updateCalls);
        static::assertNotNull($updateCalls[0]['ttl']);
        static::assertSame(120, (int) $updateCalls[0]['ttl']->getTotalSeconds());
    }

    public function testUpdateNotCalledWhenTtlValueIsNull(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(0, ResponseCode::ServerFailure, [], [], []),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('sf.example.com', RecordType::A);

        static::assertCount(0, $updateCalls);
    }

    public function testCacheKeyIsLowercaseWithColonSeparator(): void
    {
        $callCount = 0;
        $inner = self::countingResolver(static function () use (&$callCount): Response {
            $callCount++;
            return new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('case.example.com', Duration::seconds(300), Address::v4('10.0.0.1'))],
                [],
                [],
            );
        });

        $cache = new LocalStore();
        $resolver = new CachedResolver($inner, $cache);

        $resolver->query('CASE.EXAMPLE.COM', RecordType::A);
        $resolver->query('case.example.com', RecordType::A);
        $resolver->query('Case.Example.Com', RecordType::A);

        static::assertSame(1, $callCount);
    }

    public function testBothNoErrorAndNxdomainTriggerUpdateButOtherCodesDont(): void
    {
        $noErrUpdates = [];
        $storeNoErr = self::createTrackingUpdateCacheStore($noErrUpdates);
        $innerNoErr = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('ok.test', Duration::seconds(300), Address::v4('10.0.0.1'))],
                [],
                [],
            ),
        );
        $resolverNoErr = new CachedResolver($innerNoErr, $storeNoErr);
        $resolverNoErr->query('ok.test', RecordType::A);
        static::assertCount(1, $noErrUpdates);

        $nxUpdates = [];
        $storeNx = self::createTrackingUpdateCacheStore($nxUpdates);
        $innerNx = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NonExistentDomain,
                [],
                [new NSRecord('test', Duration::seconds(60), 'ns1.test')],
                [],
            ),
        );
        $resolverNx = new CachedResolver($innerNx, $storeNx);
        $resolverNx->query('nx.test', RecordType::A);
        static::assertCount(1, $nxUpdates);

        $sfUpdates = [];
        $storeSf = self::createTrackingUpdateCacheStore($sfUpdates);
        $innerSf = self::countingResolver(
            static fn(): Response => new Response(0, ResponseCode::ServerFailure, [], [], []),
        );
        $resolverSf = new CachedResolver($innerSf, $storeSf);
        $resolverSf->query('sf.test', RecordType::A);
        static::assertCount(0, $sfUpdates);
    }

    public function testUpdateKeyMatchesComputeKey(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('verify-key.example.com', Duration::seconds(300), Address::v4('10.0.0.1'))],
                [],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('verify-key.example.com', RecordType::A);

        static::assertCount(1, $updateCalls);
        static::assertSame('verify-key.example.com:1', $updateCalls[0]['key']);
    }

    public function testUpdateKeyWithEdnsContainsHashSuffix(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(
                0,
                ResponseCode::NoError,
                [new ARecord('edns-key.example.com', Duration::seconds(300), Address::v4('10.0.0.1'))],
                [],
                [],
            ),
        );

        $resolver = new CachedResolver($inner, $store);
        $option = new EDNS\NSIDOption();
        $resolver->query('edns-key.example.com', RecordType::A, ednsOptions: [$option]);

        static::assertCount(1, $updateCalls);
        $key = $updateCalls[0]['key'];
        static::assertStringStartsWith('edns-key.example.com:1:', $key);
        static::assertSame('edns-key.example.com:1:' . md5(serialize([$option])), $key);
    }

    public function testNotImplementedDoesNotTriggerUpdate(): void
    {
        $updateCalls = [];
        $store = self::createTrackingUpdateCacheStore($updateCalls);

        $inner = self::countingResolver(
            static fn(): Response => new Response(0, ResponseCode::NotImplemented, [], [], []),
        );

        $resolver = new CachedResolver($inner, $store);
        $resolver->query('notimp.example.com', RecordType::A);

        static::assertCount(0, $updateCalls);
    }

    /**
     * @param list<array{key: string, ttl: Duration|null}> $updateCalls
     */
    private static function createTrackingUpdateCacheStore(array &$updateCalls): StoreInterface
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

    /**
     * @param list<Duration|null> $computeTtls
     */
    private static function createComputeTrackingCacheStore(array &$computeTtls): StoreInterface
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
     * @param Closure(): Response $handler
     */
    private static function countingResolver(Closure $handler): object
    {
        return new class($handler) implements ResolverInterface {
            /** @var Closure(): Response */
            private Closure $handler;

            public function __construct(Closure $handler)
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
