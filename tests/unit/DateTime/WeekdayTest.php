<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Weekday;

final class WeekdayTest extends TestCase
{
    use DateTimeTestTrait;

    public function testGetPrevious(): void
    {
        static::assertSame(Weekday::Monday, Weekday::Tuesday->getPrevious());
        static::assertSame(Weekday::Tuesday, Weekday::Wednesday->getPrevious());
        static::assertSame(Weekday::Wednesday, Weekday::Thursday->getPrevious());
        static::assertSame(Weekday::Thursday, Weekday::Friday->getPrevious());
        static::assertSame(Weekday::Friday, Weekday::Saturday->getPrevious());
        static::assertSame(Weekday::Saturday, Weekday::Sunday->getPrevious());
        static::assertSame(Weekday::Sunday, Weekday::Monday->getPrevious());
    }

    public function testGetNext(): void
    {
        static::assertSame(Weekday::Tuesday, Weekday::Monday->getNext());
        static::assertSame(Weekday::Wednesday, Weekday::Tuesday->getNext());
        static::assertSame(Weekday::Thursday, Weekday::Wednesday->getNext());
        static::assertSame(Weekday::Friday, Weekday::Thursday->getNext());
        static::assertSame(Weekday::Saturday, Weekday::Friday->getNext());
        static::assertSame(Weekday::Sunday, Weekday::Saturday->getNext());
        static::assertSame(Weekday::Monday, Weekday::Sunday->getNext());
    }
}
