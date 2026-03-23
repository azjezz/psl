<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DNS\Record\RecordType;
use Psl\DNS\ResolverConvenienceMethodsTrait;
use Psl\DNS\ResolverInterface;
use Psl\DNS\Response;
use Psl\DNS\ResponseCode;
use Psl\DNSSEC\Exception\UnsignedResponseException;
use Psl\DNSSEC\SecureResolver;
use Psl\DNSSEC\TrustChainResolverInterface;
use Psl\DNSSEC\TrustChainResult;
use Psl\DNSSEC\TrustChainStatus;

final class UnsignedNegativeResponseTest extends TestCase
{
    public function testRejectsUnsignedNxdomainResponse(): void
    {
        $inner = new class() implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(1, ResponseCode::NonExistentDomain, [], [], []);
            }
        };

        $trustChain = self::createSecureTrustChainResolver();
        $resolver = new SecureResolver($inner, $trustChain);

        $this->expectException(UnsignedResponseException::class);

        $resolver->query('nonexistent.example.com', RecordType::A);
    }

    public function testRejectsUnsignedNodataResponse(): void
    {
        $inner = new class() implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(1, ResponseCode::NoError, [], [], []);
            }
        };

        $trustChain = self::createSecureTrustChainResolver();
        $resolver = new SecureResolver($inner, $trustChain);

        $this->expectException(UnsignedResponseException::class);

        $resolver->query('example.com', RecordType::AAAA);
    }

    public function testRejectsUnsignedNxdomainWithSoaButNoNsec(): void
    {
        $inner = new class() implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(1, ResponseCode::NonExistentDomain, [], [], []);
            }
        };

        $trustChain = self::createSecureTrustChainResolver();
        $resolver = new SecureResolver($inner, $trustChain);

        $this->expectException(UnsignedResponseException::class);

        $resolver->query('nonexistent.example.com', RecordType::A);
    }

    public function testInsecureZoneUnsignedNxdomainReturnsAdFalse(): void
    {
        $inner = new class() implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(1, ResponseCode::NonExistentDomain, [], [], []);
            }
        };

        $trustChain = self::createInsecureTrustChainResolver();
        $resolver = new SecureResolver($inner, $trustChain);

        $response = $resolver->query('nonexistent.insecure.com', RecordType::A);

        static::assertSame(ResponseCode::NonExistentDomain, $response->code);
        static::assertFalse($response->authenticatedData);
    }

    public function testInsecureZoneUnsignedNodataReturnsAdFalse(): void
    {
        $inner = new class() implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                return new Response(1, ResponseCode::NoError, [], [], []);
            }
        };

        $trustChain = self::createInsecureTrustChainResolver();
        $resolver = new SecureResolver($inner, $trustChain);

        $response = $resolver->query('insecure.com', RecordType::AAAA);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertFalse($response->authenticatedData);
    }

    private static function createSecureTrustChainResolver(): TrustChainResolverInterface
    {
        return new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Secure, []);
            }
        };
    }

    private static function createInsecureTrustChainResolver(): TrustChainResolverInterface
    {
        return new class() implements TrustChainResolverInterface {
            public function resolve(
                string $zone,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): TrustChainResult {
                return new TrustChainResult(TrustChainStatus::Insecure, []);
            }
        };
    }
}
