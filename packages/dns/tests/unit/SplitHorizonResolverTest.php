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
use Psl\DNS\Route;
use Psl\DNS\SplitHorizonResolver;
use Psl\IP\Address;

final class SplitHorizonResolverTest extends TestCase
{
    public function testRoutesToScopedResolver(): void
    {
        $corpResolver = self::taggedResolver(1);
        $defaultResolver = self::taggedResolver(2);

        $resolver = new SplitHorizonResolver([new Route(['corp.internal'], $corpResolver)], $defaultResolver);

        static::assertSame(1, $resolver->query('db.corp.internal', RecordType::A)->id);
    }

    public function testExactDomainMatch(): void
    {
        $corpResolver = self::taggedResolver(1);
        $defaultResolver = self::taggedResolver(2);

        $resolver = new SplitHorizonResolver([new Route(['corp.internal'], $corpResolver)], $defaultResolver);

        static::assertSame(1, $resolver->query('corp.internal', RecordType::A)->id);
    }

    public function testDoesNotMatchPartialSuffix(): void
    {
        $corpResolver = self::taggedResolver(1);
        $defaultResolver = self::taggedResolver(2);

        $resolver = new SplitHorizonResolver([new Route(['corp.internal'], $corpResolver)], $defaultResolver);

        static::assertSame(2, $resolver->query('notcorp.internal', RecordType::A)->id);
    }

    public function testFallsThroughToDefault(): void
    {
        $corpResolver = self::taggedResolver(1);
        $defaultResolver = self::taggedResolver(2);

        $resolver = new SplitHorizonResolver([new Route(['corp.internal'], $corpResolver)], $defaultResolver);

        static::assertSame(2, $resolver->query('example.com', RecordType::A)->id);
    }

    public function testMultipleRoutes(): void
    {
        $corpResolver = self::taggedResolver(1);
        $vpnResolver = self::taggedResolver(2);
        $defaultResolver = self::taggedResolver(3);

        $resolver = new SplitHorizonResolver([
            new Route(['corp.internal'], $corpResolver),
            new Route(['vpn.company.com'], $vpnResolver),
        ], $defaultResolver);

        static::assertSame(1, $resolver->query('db.corp.internal', RecordType::A)->id);
        static::assertSame(2, $resolver->query('gateway.vpn.company.com', RecordType::A)->id);
        static::assertSame(3, $resolver->query('google.com', RecordType::A)->id);
    }

    public function testMultipleDomainsPerRoute(): void
    {
        $internalResolver = self::taggedResolver(1);
        $defaultResolver = self::taggedResolver(2);

        $resolver = new SplitHorizonResolver([
            new Route(['corp.internal', 'dev.internal'], $internalResolver),
        ], $defaultResolver);

        static::assertSame(1, $resolver->query('api.corp.internal', RecordType::A)->id);
        static::assertSame(1, $resolver->query('ci.dev.internal', RecordType::A)->id);
        static::assertSame(2, $resolver->query('example.com', RecordType::A)->id);
    }

    public function testCaseInsensitiveMatching(): void
    {
        $corpResolver = self::taggedResolver(1);
        $defaultResolver = self::taggedResolver(2);

        $resolver = new SplitHorizonResolver([new Route(['Corp.Internal'], $corpResolver)], $defaultResolver);

        static::assertSame(1, $resolver->query('DB.CORP.INTERNAL', RecordType::A)->id);
        static::assertSame(1, $resolver->query('db.corp.internal', RecordType::A)->id);
    }

    public function testTrailingDotStripped(): void
    {
        $corpResolver = self::taggedResolver(1);
        $defaultResolver = self::taggedResolver(2);

        $resolver = new SplitHorizonResolver([new Route(['corp.internal'], $corpResolver)], $defaultResolver);

        static::assertSame(1, $resolver->query('db.corp.internal.', RecordType::A)->id);
    }

    public function testReverseQueryUsesDefault(): void
    {
        $corpResolver = self::taggedResolver(1);
        $defaultResolver = self::taggedResolver(2);

        $resolver = new SplitHorizonResolver([new Route(['corp.internal'], $corpResolver)], $defaultResolver);

        static::assertSame(2, $resolver->query('5.0.0.10.in-addr.arpa', RecordType::PTR)->id);
    }

    public function testNoRoutesAlwaysUsesDefault(): void
    {
        $defaultResolver = self::taggedResolver(1);

        $resolver = new SplitHorizonResolver([], $defaultResolver);

        static::assertSame(1, $resolver->query('anything.com', RecordType::A)->id);
    }

    public function testFirstMatchWins(): void
    {
        $first = self::taggedResolver(1);
        $second = self::taggedResolver(2);
        $default = self::taggedResolver(3);

        $resolver = new SplitHorizonResolver([
            new Route(['internal'], $first),
            new Route(['corp.internal'], $second),
        ], $default);

        static::assertSame(1, $resolver->query('db.corp.internal', RecordType::A)->id);
    }

    public function testDeepSubdomainMatch(): void
    {
        $corpResolver = self::taggedResolver(1);
        $defaultResolver = self::taggedResolver(2);

        $resolver = new SplitHorizonResolver([new Route(['corp.internal'], $corpResolver)], $defaultResolver);

        static::assertSame(1, $resolver->query('a.b.c.d.corp.internal', RecordType::A)->id);
    }

    private static function taggedResolver(int $id): ResolverInterface
    {
        return new class($id) implements ResolverInterface {
            public function __construct(
                private readonly int $id,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response($this->id, ResponseCode::NoError, [], [], []);
            }

            public function reverseQuery(
                Address $ip,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response($this->id, ResponseCode::NoError, [], [], []);
            }
        };
    }
}
