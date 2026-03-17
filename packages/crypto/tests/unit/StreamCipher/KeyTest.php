<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\StreamCipher;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\StreamCipher;

final class KeyTest extends TestCase
{
    public function testConstructorRejectsWrongLength(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Stream cipher key must be 16 or 32 bytes.');

        new StreamCipher\Key('');
    }

    public function testConstructorRejectsShortKey(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Stream cipher key must be 16 or 32 bytes.');

        new StreamCipher\Key('x');
    }
}
