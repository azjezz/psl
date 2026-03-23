<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\NSEC3PARAMRecord;
use Psl\DNS\Record\RecordType;

final class Nsec3paramRecordTest extends TestCase
{
    public function testProperties(): void
    {
        $record = new NSEC3PARAMRecord('example.com', Duration::seconds(3600), 1, 0, 10, 'aabb');

        static::assertSame(RecordType::NSEC3PARAM, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame(1, $record->hashAlgorithm);
        static::assertSame(0, $record->flags);
        static::assertSame(10, $record->iterations);
        static::assertSame('aabb', $record->salt);
    }
}
