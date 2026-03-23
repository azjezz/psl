<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DNS\Record\RecordType;
use Psl\DNS\ResolverConvenienceMethodsTrait;
use Psl\DNS\ResolverInterface;
use Psl\DNS\Response;
use Psl\DNS\ResponseCode;
use Psl\IP\Address;

final class ResolverConvenienceMethodsTraitTest extends TestCase
{
    public function testReverseQueryIpv4(): void
    {
        $capturedName = null;
        $capturedKind = null;

        $resolver = $this->createCapturingResolver($capturedName, $capturedKind);
        $resolver->reverseQuery(Address::v4('192.168.1.10'));

        static::assertSame('10.1.168.192.in-addr.arpa', $capturedName);
        static::assertSame(RecordType::PTR, $capturedKind);
    }

    public function testReverseQueryIpv6Full(): void
    {
        $capturedName = null;
        $capturedKind = null;

        $resolver = $this->createCapturingResolver($capturedName, $capturedKind);
        $resolver->reverseQuery(Address::v6('2001:0db8:0000:0000:0000:0000:0000:0001'));

        $expected = '1.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa';
        static::assertSame($expected, $capturedName);
        static::assertSame(RecordType::PTR, $capturedKind);
    }

    public function testReverseQueryIpv6Abbreviated(): void
    {
        $capturedName = null;
        $capturedKind = null;

        $resolver = $this->createCapturingResolver($capturedName, $capturedKind);
        $resolver->reverseQuery(Address::v6('2001:db8::1'));

        $expected = '1.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa';
        static::assertSame($expected, $capturedName);
    }

    public function testReverseQueryIpv6Loopback(): void
    {
        $capturedName = null;
        $capturedKind = null;

        $resolver = $this->createCapturingResolver($capturedName, $capturedKind);
        $resolver->reverseQuery(Address::v6('::1'));

        $expected = '1.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.ip6.arpa';
        static::assertSame($expected, $capturedName);
    }

    public function testReverseQueryIpv6AllZeros(): void
    {
        $capturedName = null;
        $capturedKind = null;

        $resolver = $this->createCapturingResolver($capturedName, $capturedKind);
        $resolver->reverseQuery(Address::v6('::'));

        $expected = '0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.ip6.arpa';
        static::assertSame($expected, $capturedName);
    }

    public function testReverseQueryIpv6TrailingDoubleColon(): void
    {
        $capturedName = null;
        $capturedKind = null;

        $resolver = $this->createCapturingResolver($capturedName, $capturedKind);
        $resolver->reverseQuery(Address::v6('2001:db8::'));

        $expected = '0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.d.0.1.0.0.2.ip6.arpa';
        static::assertSame($expected, $capturedName);
    }

    public function testReverseQueryIpv6MiddleDoubleColon(): void
    {
        $capturedName = null;
        $capturedKind = null;

        $resolver = $this->createCapturingResolver($capturedName, $capturedKind);
        $resolver->reverseQuery(Address::v6('fe80::1:2'));

        $expected = '2.0.0.0.1.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.e.f.ip6.arpa';
        static::assertSame($expected, $capturedName);
    }

    private function createCapturingResolver(
        string|null &$capturedName,
        RecordType|null &$capturedKind,
    ): ResolverInterface {
        return new class($capturedName, $capturedKind) implements ResolverInterface {
            use ResolverConvenienceMethodsTrait;

            public function __construct(
                private string|null &$capturedName,
                private RecordType|null &$capturedKind,
            ) {}

            public function query(
                string $name,
                RecordType $type,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
                array $ednsOptions = [],
            ): Response {
                $this->capturedName = $name;
                $this->capturedKind = $type;

                return new Response(1, ResponseCode::NoError, [], [], []);
            }
        };
    }
}
