<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DNS\HostsFileResolver;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\RecordType;
use Psl\DNS\ResolverInterface;
use Psl\DNS\Response;
use Psl\DNS\ResponseCode;
use Psl\DNS\System\HostsFile\HostsFile;
use Psl\IP\Address;
use RuntimeException;

final class HostsFileResolverTest extends TestCase
{
    public function testResolvesARecordFromHostsFile(): void
    {
        $hostsFile = new HostsFile([
            'db.local' => [Address::parse('192.168.1.50')],
        ]);

        $inner = self::neverCalledResolver();
        $resolver = new HostsFileResolver($inner, $hostsFile);

        $response = $resolver->query('db.local', RecordType::A);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertCount(1, $response->answers);
        static::assertSame('192.168.1.50', $response->getFirstAnswerRecord(ARecord::class)?->address->toString());
    }

    public function testResolvesAAAARecordFromHostsFile(): void
    {
        $hostsFile = new HostsFile([
            'localhost' => [Address::parse('::1')],
        ]);

        $inner = self::neverCalledResolver();
        $resolver = new HostsFileResolver($inner, $hostsFile);

        $response = $resolver->query('localhost', RecordType::AAAA);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertCount(1, $response->answers);
    }

    public function testFiltersV4ForAQuery(): void
    {
        $hostsFile = new HostsFile([
            'localhost' => [Address::parse('127.0.0.1'), Address::parse('::1')],
        ]);

        $inner = self::neverCalledResolver();
        $resolver = new HostsFileResolver($inner, $hostsFile);

        $response = $resolver->query('localhost', RecordType::A);

        static::assertCount(1, $response->answers);
    }

    public function testFiltersV6ForAAAAQuery(): void
    {
        $hostsFile = new HostsFile([
            'localhost' => [Address::parse('127.0.0.1'), Address::parse('::1')],
        ]);

        $inner = self::neverCalledResolver();
        $resolver = new HostsFileResolver($inner, $hostsFile);

        $response = $resolver->query('localhost', RecordType::AAAA);

        static::assertCount(1, $response->answers);
    }

    public function testDelegatesToInnerOnMiss(): void
    {
        $hostsFile = new HostsFile([]);

        $inner = self::taggedResolver(42);
        $resolver = new HostsFileResolver($inner, $hostsFile);

        $response = $resolver->query('example.com', RecordType::A);

        static::assertSame(42, $response->id);
    }

    public function testDelegatesToInnerForNonARecord(): void
    {
        $hostsFile = new HostsFile([
            'mail.local' => [Address::parse('10.0.0.1')],
        ]);

        $inner = self::taggedResolver(99);
        $resolver = new HostsFileResolver($inner, $hostsFile);

        $response = $resolver->query('mail.local', RecordType::MX);

        static::assertSame(99, $response->id);
    }

    public function testDelegatesToInnerWhenNoMatchingFamily(): void
    {
        $hostsFile = new HostsFile([
            'v4only.local' => [Address::parse('10.0.0.1')],
        ]);

        $inner = self::taggedResolver(77);
        $resolver = new HostsFileResolver($inner, $hostsFile);

        $response = $resolver->query('v4only.local', RecordType::AAAA);

        static::assertSame(77, $response->id);
    }

    public function testCaseInsensitiveLookup(): void
    {
        $hostsFile = new HostsFile([
            'myapp.local' => [Address::parse('192.168.1.100')],
        ]);

        $inner = self::neverCalledResolver();
        $resolver = new HostsFileResolver($inner, $hostsFile);

        $response = $resolver->query('MYAPP.LOCAL', RecordType::A);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertCount(1, $response->answers);
    }

    public function testMultipleAddressesReturned(): void
    {
        $hostsFile = new HostsFile([
            'db.local' => [Address::parse('10.0.0.1'), Address::parse('10.0.0.2'), Address::parse('10.0.0.3')],
        ]);

        $inner = self::neverCalledResolver();
        $resolver = new HostsFileResolver($inner, $hostsFile);

        $response = $resolver->query('db.local', RecordType::A);

        static::assertCount(3, $response->answers);
    }

    public function testPTRQueryDelegatesToInner(): void
    {
        $hostsFile = new HostsFile([
            'db.local' => [Address::parse('10.0.0.1')],
        ]);

        $inner = self::taggedResolver(55);
        $resolver = new HostsFileResolver($inner, $hostsFile);

        $response = $resolver->query('1.0.0.10.in-addr.arpa', RecordType::PTR);

        static::assertSame(55, $response->id);
    }

    private static function neverCalledResolver(): ResolverInterface
    {
        return new class implements ResolverInterface {
            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                throw new RuntimeException('Inner resolver should not have been called');
            }

            public function reverseQuery(
                Address $ip,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                throw new RuntimeException('Inner resolver should not have been called');
            }
        };
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
