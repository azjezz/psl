<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Asymmetric;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Asymmetric;
use Psl\Crypto\Exception;

final class SecretKeyTest extends TestCase
{
    public function testConstructorRejectsWrongLength(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Asymmetric encryption secret key must be exactly 32 bytes.');

        new Asymmetric\SecretKey('');
    }

    public function testConstructorRejectsShortKey(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Asymmetric encryption secret key must be exactly 32 bytes.');

        new Asymmetric\SecretKey('x');
    }
}
