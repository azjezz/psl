<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Cache\LocalStore;
use Psl\DateTime\Duration;
use Psl\DNS\CachedResolver;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\PTRRecord;
use Psl\DNS\Record\RecordType;
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
}
