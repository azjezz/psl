<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\Record;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DNS\Record\NSEC3Record;
use Psl\DNS\Record\RecordType;

final class Nsec3RecordTest extends TestCase
{
    public function testProperties(): void
    {
        $types = [RecordType::A, RecordType::AAAA];
        $record = new NSEC3Record('example.com', Duration::seconds(3600), 1, 0, 10, 'aabb', 'CPNMU', $types);

        static::assertSame(RecordType::NSEC3, $record->kind);
        static::assertSame('example.com', $record->name);
        static::assertEquals(Duration::seconds(3600), $record->duration);
        static::assertSame(1, $record->hashAlgorithm);
        static::assertSame(0, $record->flags);
        static::assertSame(10, $record->iterations);
        static::assertSame('aabb', $record->salt);
        static::assertSame('CPNMU', $record->nextHashedOwnerName);
        static::assertSame($types, $record->types);
    }
}
