<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DNS\Record\RecordType;
use Psl\DNS\ResolverInterface;
use Psl\DNS\Response;
use Psl\DNS\ResponseCode;
use Psl\DNS\SearchDomainResolver;
use Psl\IP\Address;

final class SearchDomainResolverTest extends TestCase
{
    public function testShortNameExpandedWithSearchDomain(): void
    {
        $inner = self::trackingResolver([
            'db.example.com' => ResponseCode::NoError,
        ]);

        $resolver = new SearchDomainResolver($inner, ['example.com']);

        $response = $resolver->query('db', RecordType::A);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertSame('db.example.com', $inner->lastQueried);
    }

    public function testTriesSearchDomainsInOrder(): void
    {
        $inner = self::trackingResolver([
            'db.first.com' => ResponseCode::NonExistentDomain,
            'db.second.com' => ResponseCode::NoError,
        ]);

        $resolver = new SearchDomainResolver($inner, ['first.com', 'second.com']);

        $response = $resolver->query('db', RecordType::A);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertSame('db.second.com', $inner->lastQueried);
    }

    public function testFallsBackToOriginalNameIfAllFail(): void
    {
        $inner = self::trackingResolver([
            'db.first.com' => ResponseCode::NonExistentDomain,
            'db.second.com' => ResponseCode::NonExistentDomain,
            'db' => ResponseCode::NonExistentDomain,
        ]);

        $resolver = new SearchDomainResolver($inner, ['first.com', 'second.com']);

        $response = $resolver->query('db', RecordType::A);

        static::assertSame(ResponseCode::NonExistentDomain, $response->code);
        static::assertSame('db', $inner->lastQueried);
    }

    public function testFqdnSkipsSearchDomains(): void
    {
        $inner = self::trackingResolver([
            'db.example.com' => ResponseCode::NoError,
        ]);

        $resolver = new SearchDomainResolver($inner, ['other.com']);

        $response = $resolver->query('db.example.com', RecordType::A);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertSame('db.example.com', $inner->lastQueried);
    }

    public function testTrailingDotSkipsSearchDomains(): void
    {
        $inner = self::trackingResolver([
            'db.' => ResponseCode::NoError,
        ]);

        $resolver = new SearchDomainResolver($inner, ['example.com']);

        $response = $resolver->query('db.', RecordType::A);

        static::assertSame('db.', $inner->lastQueried);
    }

    public function testNdotsThreshold(): void
    {
        $inner = self::trackingResolver([
            'a.b' => ResponseCode::NoError,
        ]);

        $resolver = new SearchDomainResolver($inner, ['example.com'], numberOfDots: 2);

        $response = $resolver->query('a.b', RecordType::A);

        static::assertSame('a.b.example.com', $inner->firstQueried);
    }

    public function testNdotsMetSkipsExpansion(): void
    {
        $inner = self::trackingResolver([
            'a.b.c' => ResponseCode::NoError,
        ]);

        $resolver = new SearchDomainResolver($inner, ['example.com'], numberOfDots: 2);

        $response = $resolver->query('a.b.c', RecordType::A);

        static::assertSame('a.b.c', $inner->lastQueried);
    }

    public function testEmptySearchDomainsPassesThrough(): void
    {
        $inner = self::trackingResolver([
            'db' => ResponseCode::NoError,
        ]);

        $resolver = new SearchDomainResolver($inner, []);

        $response = $resolver->query('db', RecordType::A);

        static::assertSame('db', $inner->lastQueried);
        static::assertSame(1, $inner->queryCount);
    }

    public function testStopsOnFirstNonNxdomain(): void
    {
        $inner = self::trackingResolver([
            'db.first.com' => ResponseCode::ServerFailure,
        ]);

        $resolver = new SearchDomainResolver($inner, ['first.com', 'second.com']);

        $response = $resolver->query('db', RecordType::A);

        static::assertSame(ResponseCode::ServerFailure, $response->code);
        static::assertSame(1, $inner->queryCount);
    }

    public function testMultipleSearchDomainsAllNxdomain(): void
    {
        $inner = self::trackingResolver([
            'host.a.com' => ResponseCode::NonExistentDomain,
            'host.b.com' => ResponseCode::NonExistentDomain,
            'host.c.com' => ResponseCode::NonExistentDomain,
            'host' => ResponseCode::NonExistentDomain,
        ]);

        $resolver = new SearchDomainResolver($inner, ['a.com', 'b.com', 'c.com']);

        $resolver->query('host', RecordType::A);

        static::assertSame(4, $inner->queryCount);
    }

    /**
     * @param array<string, ResponseCode> $responses
     */
    private static function trackingResolver(array $responses): object
    {
        return new class($responses) implements ResolverInterface {
            public null|string $lastQueried = null;
            public null|string $firstQueried = null;
            public int $queryCount = 0;

            /**
             * @param array<string, ResponseCode> $responses
             */
            public function __construct(
                private readonly array $responses,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                $this->queryCount++;
                $this->lastQueried = $name;
                if ($this->firstQueried === null) {
                    $this->firstQueried = $name;
                }

                $code = $this->responses[$name] ?? ResponseCode::NonExistentDomain;

                return new Response(0, $code, [], [], []);
            }

            public function reverseQuery(
                Address $ip,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(0, ResponseCode::NoError, [], [], []);
            }
        };
    }
}
