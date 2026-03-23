<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\SSHFP\Algorithm;
use Psl\DNS\Record\SSHFP\FingerprintType;
use Psl\DNS\Record\SSHFPRecord;

final class SshfpRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new SSHFPRecord(
            'example.com',
            Duration::seconds(3600),
            Algorithm::RSA,
            FingerprintType::SHA1,
            'aabbccdd',
        );

        static::assertSame(RecordType::SSHFP, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame(Algorithm::RSA, $record->algorithm);
        static::assertSame(FingerprintType::SHA1, $record->fingerprintType);
        static::assertSame('aabbccdd', $record->fingerprint);
    }
}
