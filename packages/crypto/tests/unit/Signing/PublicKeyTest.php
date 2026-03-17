<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Signing;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\Signing;

final class PublicKeyTest extends TestCase
{
    public function testConstructorRejectsWrongLength(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Signing public key must be exactly 32 bytes.');

        new Signing\PublicKey('');
    }

    public function testConstructorRejectsShortKey(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Signing public key must be exactly 32 bytes.');

        new Signing\PublicKey('x');
    }
}
