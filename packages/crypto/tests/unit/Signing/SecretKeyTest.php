<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Signing;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\Signing;

final class SecretKeyTest extends TestCase
{
    public function testConstructorRejectsWrongLength(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Signing secret key must be exactly 64 bytes.');

        new Signing\SecretKey('');
    }

    public function testConstructorRejectsShortKey(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Signing secret key must be exactly 64 bytes.');

        new Signing\SecretKey('x');
    }
}
