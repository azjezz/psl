<?php

declare(strict_types=1);

namespace Psl\MIME\Tests\Unit\Internal\CMS;

use PHPUnit\Framework\TestCase;
use Psl\MIME\Internal\CMS\OutputEncoder;
use Psl\MIME\SMIME\Encoding;

final class OutputEncoderTest extends TestCase
{
    public function testDerPassthrough(): void
    {
        $der = "\x30\x03\x02\x01\x42";

        $encoded = OutputEncoder::encode($der, Encoding::DER, 'signed-data');
        static::assertSame($der, $encoded);

        $decoded = OutputEncoder::decode($encoded, Encoding::DER);
        static::assertSame($der, $decoded);
    }

    public function testPemRoundTrip(): void
    {
        $der = "\x30\x03\x02\x01\x42";

        $pem = OutputEncoder::encode($der, Encoding::PEM, 'signed-data');
        static::assertStringContainsString('-----BEGIN CMS-----', $pem);
        static::assertStringContainsString('-----END CMS-----', $pem);

        $decoded = OutputEncoder::decode($pem, Encoding::PEM);
        static::assertSame($der, $decoded);
    }

    public function testSmimeRoundTrip(): void
    {
        $der = "\x30\x03\x02\x01\x42";

        $smime = OutputEncoder::encode($der, Encoding::SMIME, 'signed-data');
        static::assertStringContainsString('MIME-Version: 1.0', $smime);
        static::assertStringContainsString('Content-Type: application/pkcs7-mime', $smime);
        static::assertStringContainsString('smime-type=signed-data', $smime);
        static::assertStringContainsString('Content-Transfer-Encoding: base64', $smime);

        $decoded = OutputEncoder::decode($smime, Encoding::SMIME);
        static::assertSame($der, $decoded);
    }

    public function testSmimeEnvelopedDataType(): void
    {
        $der = "\x30\x03\x02\x01\x42";

        $smime = OutputEncoder::encode($der, Encoding::SMIME, 'enveloped-data');
        static::assertStringContainsString('smime-type=enveloped-data', $smime);
    }
}
