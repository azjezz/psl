<?php

declare(strict_types=1);

namespace Psl\Crypto\Tests\Unit\Signing;

use PHPUnit\Framework\TestCase;
use Psl\Crypto\Exception;
use Psl\Crypto\Signing;

final class SignatureTest extends TestCase
{
    public function testConstructorRejectsWrongLength(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Signature must be exactly 64 bytes.');

        new Signing\Signature('');
    }

    public function testConstructorRejectsShortSignature(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Signature must be exactly 64 bytes.');

        new Signing\Signature('x');
    }
}
