<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Hash;

use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Hash;

final class ContextTest extends TestCase
{
    public function testForAlgorithm(): void
    {
        $context = Hash\Context::forAlgorithm('md5')
            ->update('The quick brown fox ')
            ->update('jumped over the lazy dog.');

        static::assertSame('5c6ffbdd40d9556b73a21e63c3e0e904', $context->finalize());
    }

    public function testForAlgorithmThrowsForInvalidAlgorithm(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Expected a valid hashing algorithm, "base64" given.');

        Hash\Context::forAlgorithm('base64');
    }

    public function testHmac(): void
    {
        $context = Hash\Context::hmac('md5', 'secret')
            ->update('The quick brown fox ')
            ->update('jumped over the lazy dog.');

        static::assertSame('7eb2b5c37443418fc77c136dd20e859c', $context->finalize());
    }

    public function testHmacThrowsForInvalidAlgorithm(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Expected a hashing algorithms suitable for HMAC, "base64" given.');

        Hash\Context::hmac('base64', 'secret');
    }

    public function testHmacThrowsForEmptySecretKey(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Expected a non-empty shared secret key.');

        Hash\Context::hmac('sha1', '');
    }

    public function testContextIsImmutable(): void
    {
        $first  = Hash\Context::forAlgorithm('md5');
        $second = $first->update('The quick brown fox ');
        $third  = $second->update('jumped over the lazy dog.');

        static::assertNotSame($first, $second);
        static::assertNotSame($second, $third);
        static::assertNotSame($third, $first);

        static::assertSame('d41d8cd98f00b204e9800998ecf8427e', $first->finalize());
        static::assertSame('c4314972a672ded8759cafdca9af3238', $second->finalize());
        static::assertSame('5c6ffbdd40d9556b73a21e63c3e0e904', $third->finalize());
    }

    public function testContextIsStillValidAfterFinalization(): void
    {
        $context  = Hash\Context::forAlgorithm('md5')
            ->update('The quick brown fox ')
            ->update('jumped over the lazy dog.');

        static::assertSame('5c6ffbdd40d9556b73a21e63c3e0e904', $context->finalize());
        static::assertSame('5983132dd3e26f51fa8611a94c8e05ac', $context->update(' cool!')->finalize());
    }
}
