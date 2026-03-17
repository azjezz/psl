<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\KeyExchange;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\KeyExchange;

final class PublicKeyTest extends TestCase
{
    public function testConstructorRejectsWrongLength(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key exchange public key must be exactly 32 bytes.');

        new KeyExchange\PublicKey('');
    }

    public function testConstructorRejectsShortKey(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key exchange public key must be exactly 32 bytes.');

        new KeyExchange\PublicKey('x');
    }
}
