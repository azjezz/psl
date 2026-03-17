<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Aead;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Aead;
use Psl\Crypto\Exception;

final class KeyTest extends TestCase
{
    public function testConstructorRejectsWrongLength(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('AEAD key must be exactly 32 bytes.');

        new Aead\Key('');
    }

    public function testConstructorRejectsShortKey(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('AEAD key must be exactly 32 bytes.');

        new Aead\Key('x');
    }
}
