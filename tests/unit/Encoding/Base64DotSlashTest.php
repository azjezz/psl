<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Encoding;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Encoding\Base64;

final class Base64DotSlashTest extends TestCase
{
    public function testEncodeNotImplemented(): void
    {
        $this->expectException(Exception::class);

        Base64\encode('binary', Base64\Variant::DotSlashOrdered);
    }

    public function testDecodeNotImplemented(): void
    {
        $this->expectException(Exception::class);

        Base64\decode('abcd', Base64\Variant::DotSlashOrdered);
    }
}
