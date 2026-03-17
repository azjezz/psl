<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Kdf;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\Kdf;

final class KeyTest extends TestCase
{
    public function testConstructorRejectsWrongLength(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('KDF key must be exactly 32 bytes.');

        new Kdf\Key('');
    }

    public function testConstructorRejectsShortKey(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('KDF key must be exactly 32 bytes.');

        new Kdf\Key('x');
    }
}
