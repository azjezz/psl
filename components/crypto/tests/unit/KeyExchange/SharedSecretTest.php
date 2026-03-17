<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\KeyExchange;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\KeyExchange;

final class SharedSecretTest extends TestCase
{
    public function testConstructorRejectsWrongLength(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Shared secret must be exactly 32 bytes.');

        new KeyExchange\SharedSecret('');
    }

    public function testConstructorRejectsShortKey(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Shared secret must be exactly 32 bytes.');

        new KeyExchange\SharedSecret('x');
    }
}
