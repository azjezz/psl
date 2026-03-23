<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\AAAARecord;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\MXRecord;
use Psl\DNS\Record\PTRRecord;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\TXTRecord;
use Psl\DNS\ResponseCode;
use Psl\DNS\StaticResolver;
use Psl\IP\Address;

final class StaticResolverTest extends TestCase
{
    public function testReturnsNxdomainForUnknownName(): void
    {
        $resolver = new StaticResolver([]);

        $response = $resolver->query('example.com', RecordType::A);

        static::assertSame(ResponseCode::NonExistentDomain, $response->code);
        static::assertSame([], $response->answers);
    }

    public function testReturnsEmptyAnswersForKnownNameButUnknownKind(): void
    {
        $resolver = new StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::zero(), Address::v4('1.2.3.4')),
                ],
            ],
        ]);

        $response = $resolver->query('example.com', RecordType::AAAA);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertSame([], $response->answers);
    }

    public function testReturnsARecords(): void
    {
        $a1 = new ARecord('example.com', Duration::zero(), Address::v4('1.2.3.4'));
        $a2 = new ARecord('example.com', Duration::zero(), Address::v4('5.6.7.8'));

        $resolver = new StaticResolver([
            'example.com' => [
                RecordType::A->value => [$a1, $a2],
            ],
        ]);

        $response = $resolver->query('example.com', RecordType::A);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertSame([$a1, $a2], $response->answers);
        static::assertSame([], $response->authority);
        static::assertSame([], $response->additional);
    }

    public function testReturnsAaaaRecords(): void
    {
        $aaaa = new AAAARecord('example.com', Duration::zero(), Address::v6('::1'));

        $resolver = new StaticResolver([
            'example.com' => [
                RecordType::AAAA->value => [$aaaa],
            ],
        ]);

        $response = $resolver->query('example.com', RecordType::AAAA);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertSame([$aaaa], $response->answers);
    }

    public function testReturnsTxtRecords(): void
    {
        $txt = new TXTRecord('example.com', Duration::zero(), ['v=spf1 -all']);

        $resolver = new StaticResolver([
            'example.com' => [
                RecordType::TXT->value => [$txt],
            ],
        ]);

        $response = $resolver->query('example.com', RecordType::TXT);

        static::assertSame([$txt], $response->answers);
    }

    public function testReturnsMxRecords(): void
    {
        $mx = new MXRecord('example.com', Duration::zero(), 10, 'mail.example.com');

        $resolver = new StaticResolver([
            'example.com' => [
                RecordType::MX->value => [$mx],
            ],
        ]);

        $response = $resolver->query('example.com', RecordType::MX);

        static::assertSame([$mx], $response->answers);
    }

    public function testMultipleKindsForSameName(): void
    {
        $a = new ARecord('example.com', Duration::zero(), Address::v4('1.2.3.4'));
        $aaaa = new AAAARecord('example.com', Duration::zero(), Address::v6('::1'));
        $txt = new TXTRecord('example.com', Duration::zero(), ['hello']);

        $resolver = new StaticResolver([
            'example.com' => [
                RecordType::A->value => [$a],
                RecordType::AAAA->value => [$aaaa],
                RecordType::TXT->value => [$txt],
            ],
        ]);

        static::assertSame([$a], $resolver->query('example.com', RecordType::A)->answers);
        static::assertSame([$aaaa], $resolver->query('example.com', RecordType::AAAA)->answers);
        static::assertSame([$txt], $resolver->query('example.com', RecordType::TXT)->answers);
    }

    public function testMultipleNames(): void
    {
        $a1 = new ARecord('one.example.com', Duration::zero(), Address::v4('1.1.1.1'));
        $a2 = new ARecord('two.example.com', Duration::zero(), Address::v4('2.2.2.2'));

        $resolver = new StaticResolver([
            'one.example.com' => [
                RecordType::A->value => [$a1],
            ],
            'two.example.com' => [
                RecordType::A->value => [$a2],
            ],
        ]);

        static::assertSame([$a1], $resolver->query('one.example.com', RecordType::A)->answers);
        static::assertSame([$a2], $resolver->query('two.example.com', RecordType::A)->answers);
    }

    public function testNameLookupIsCaseInsensitive(): void
    {
        $a = new ARecord('example.com', Duration::zero(), Address::v4('1.2.3.4'));

        $resolver = new StaticResolver([
            'Example.COM' => [
                RecordType::A->value => [$a],
            ],
        ]);

        static::assertSame([$a], $resolver->query('example.com', RecordType::A)->answers);
        static::assertSame([$a], $resolver->query('EXAMPLE.COM', RecordType::A)->answers);
        static::assertSame([$a], $resolver->query('Example.Com', RecordType::A)->answers);
    }

    public function testResponseIdIsAlwaysZero(): void
    {
        $resolver = new StaticResolver([
            'example.com' => [
                RecordType::A->value => [new ARecord('example.com', Duration::zero(), Address::v4('1.2.3.4'))],
            ],
        ]);

        static::assertSame(0, $resolver->query('example.com', RecordType::A)->id);
        static::assertSame(0, $resolver->query('unknown.com', RecordType::A)->id);
    }

    public function testReverseQueryForIpv4(): void
    {
        $ptr = new PTRRecord('4.3.2.1.in-addr.arpa', Duration::zero(), 'example.com');

        $resolver = new StaticResolver([
            '4.3.2.1.in-addr.arpa' => [
                RecordType::PTR->value => [$ptr],
            ],
        ]);

        $response = $resolver->reverseQuery(Address::v4('1.2.3.4'));

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertSame([$ptr], $response->answers);
    }

    public function testReverseQueryForIpv6(): void
    {
        $arpaName = '1.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.ip6.arpa';
        $ptr = new PTRRecord($arpaName, Duration::zero(), 'localhost');

        $resolver = new StaticResolver([
            $arpaName => [
                RecordType::PTR->value => [$ptr],
            ],
        ]);

        $response = $resolver->reverseQuery(Address::v6('::1'));

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertSame([$ptr], $response->answers);
    }

    public function testEdnsOptionsAreIgnored(): void
    {
        $a = new ARecord('example.com', Duration::zero(), Address::v4('1.2.3.4'));

        $resolver = new StaticResolver([
            'example.com' => [
                RecordType::A->value => [$a],
            ],
        ]);

        $response = $resolver->query('example.com', RecordType::A, ednsOptions: []);

        static::assertSame([$a], $response->answers);
    }

    public function testEmptyRecordList(): void
    {
        $resolver = new StaticResolver([
            'example.com' => [
                RecordType::A->value => [],
            ],
        ]);

        $response = $resolver->query('example.com', RecordType::A);

        static::assertSame(ResponseCode::NoError, $response->code);
        static::assertSame([], $response->answers);
    }
}
