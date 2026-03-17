<?php

declare(strict_types=1);

namespace Psl\TLS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\TLS\Certificate;

final class CertificateTest extends TestCase
{
    public function testCreate(): void
    {
        $cert = Certificate::create('/path/to/cert.pem', '/path/to/key.pem');

        static::assertSame('/path/to/cert.pem', $cert->certificateFile);
        static::assertSame('/path/to/key.pem', $cert->keyFile);
        static::assertNull($cert->passphrase);
    }

    public function testCreateWithPassphrase(): void
    {
        $cert = Certificate::create('/path/to/cert.pem', '/path/to/key.pem', 'secret');

        static::assertSame('/path/to/cert.pem', $cert->certificateFile);
        static::assertSame('/path/to/key.pem', $cert->keyFile);
        static::assertSame('secret', $cert->passphrase);
    }

    public function testConstructor(): void
    {
        $cert = new Certificate('/cert.pem', '/key.pem', 'pass');

        static::assertSame('/cert.pem', $cert->certificateFile);
        static::assertSame('/key.pem', $cert->keyFile);
        static::assertSame('pass', $cert->passphrase);
    }
}
