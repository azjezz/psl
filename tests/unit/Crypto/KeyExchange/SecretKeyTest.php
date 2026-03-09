<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Crypto\KeyExchange;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\KeyExchange;

final class SecretKeyTest extends TestCase
{
    public function testConstructorRejectsWrongLength(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key exchange secret key must be exactly 32 bytes.');

        new KeyExchange\SecretKey('');
    }

    public function testConstructorRejectsShortKey(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key exchange secret key must be exactly 32 bytes.');

        new KeyExchange\SecretKey('x');
    }
}
