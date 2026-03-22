<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\SMTP\Capability;

final class CapabilityTest extends TestCase
{
    public function testEightBitMIMEValue(): void
    {
        static::assertSame('8BITMIME', Capability::EightBitMIME->value);
    }

    public function testSizeValue(): void
    {
        static::assertSame('SIZE', Capability::Size->value);
    }

    public function testStartTLSValue(): void
    {
        static::assertSame('STARTTLS', Capability::StartTLS->value);
    }

    public function testPipeliningValue(): void
    {
        static::assertSame('PIPELINING', Capability::Pipelining->value);
    }

    public function testDSNValue(): void
    {
        static::assertSame('DSN', Capability::DSN->value);
    }

    public function testSMTPUTF8Value(): void
    {
        static::assertSame('SMTPUTF8', Capability::SMTPUTF8->value);
    }

    #[DataProvider('validCapabilityProvider')]
    public function testFromValidValue(string $value, Capability $expected): void
    {
        static::assertSame($expected, Capability::from($value));
    }

    /**
     * @return iterable<string, array{string, Capability}>
     */
    public static function validCapabilityProvider(): iterable
    {
        yield '8BITMIME' => ['8BITMIME', Capability::EightBitMIME];
        yield 'SIZE' => ['SIZE', Capability::Size];
        yield 'STARTTLS' => ['STARTTLS', Capability::StartTLS];
        yield 'PIPELINING' => ['PIPELINING', Capability::Pipelining];
        yield 'DSN' => ['DSN', Capability::DSN];
        yield 'SMTPUTF8' => ['SMTPUTF8', Capability::SMTPUTF8];
    }

    #[DataProvider('validTryFromProvider')]
    public function testTryFromValidValue(string $value, Capability $expected): void
    {
        static::assertSame($expected, Capability::tryFrom($value));
    }

    /**
     * @return iterable<string, array{string, Capability}>
     */
    public static function validTryFromProvider(): iterable
    {
        yield '8BITMIME' => ['8BITMIME', Capability::EightBitMIME];
        yield 'SIZE' => ['SIZE', Capability::Size];
        yield 'STARTTLS' => ['STARTTLS', Capability::StartTLS];
        yield 'PIPELINING' => ['PIPELINING', Capability::Pipelining];
        yield 'DSN' => ['DSN', Capability::DSN];
        yield 'SMTPUTF8' => ['SMTPUTF8', Capability::SMTPUTF8];
        yield 'CHUNKING' => ['CHUNKING', Capability::Chunking];
        yield 'BINARYMIME' => ['BINARYMIME', Capability::BinaryMIME];
        yield 'REQUIRETLS' => ['REQUIRETLS', Capability::RequireTLS];
        yield 'DELIVERBY' => ['DELIVERBY', Capability::DeliverBy];
        yield 'FUTURERELEASE' => ['FUTURERELEASE', Capability::FutureRelease];
        yield 'MT-PRIORITY' => ['MT-PRIORITY', Capability::MTPriority];
    }

    #[DataProvider('invalidTryFromProvider')]
    public function testTryFromInvalidValue(string $value): void
    {
        static::assertNull(Capability::tryFrom($value));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidTryFromProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'lowercase 8bitmime' => ['8bitmime'];
        yield 'lowercase size' => ['size'];
        yield 'lowercase starttls' => ['starttls'];
        yield 'lowercase pipelining' => ['pipelining'];
        yield 'lowercase dsn' => ['dsn'];
        yield 'lowercase smtputf8' => ['smtputf8'];
        yield 'AUTH' => ['AUTH'];
        yield 'ENHANCEDSTATUSCODES' => ['ENHANCEDSTATUSCODES'];
        yield 'random' => ['NOTACAPABILITY'];
        yield 'partial match' => ['SIZE1024'];
    }

    public function testAllCasesCount(): void
    {
        static::assertCount(12, Capability::cases());
    }

    public function testCasesContainsAllExpected(): void
    {
        $cases = Capability::cases();

        static::assertContains(Capability::EightBitMIME, $cases);
        static::assertContains(Capability::Size, $cases);
        static::assertContains(Capability::StartTLS, $cases);
        static::assertContains(Capability::Pipelining, $cases);
        static::assertContains(Capability::DSN, $cases);
        static::assertContains(Capability::SMTPUTF8, $cases);
        static::assertContains(Capability::Chunking, $cases);
        static::assertContains(Capability::BinaryMIME, $cases);
        static::assertContains(Capability::RequireTLS, $cases);
        static::assertContains(Capability::DeliverBy, $cases);
        static::assertContains(Capability::FutureRelease, $cases);
        static::assertContains(Capability::MTPriority, $cases);
    }
}
