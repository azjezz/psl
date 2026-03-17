<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Symmetric;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\Symmetric;

final class KeyTest extends TestCase
{
    public function testConstructorRejectsWrongLength(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Encryption key must be exactly 32 bytes.');

        new Symmetric\Key('');
    }

    public function testConstructorRejectsShortKey(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Encryption key must be exactly 32 bytes.');

        new Symmetric\Key('x');
    }
}
