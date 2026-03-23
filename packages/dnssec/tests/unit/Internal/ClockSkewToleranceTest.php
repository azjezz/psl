<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\DateTime\Timestamp;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\Record\RecordType;
use Psl\DNS\Record\RRSIGRecord;
use Psl\DNSSEC\Internal\RRSIG\RRSIGVerifier;
use Psl\Str;

final class ClockSkewToleranceTest extends TestCase
{
    public function testRejectsExpiredRrsigWithoutSkew(): void
    {
        $now = Timestamp::now()->getSeconds();
        $rrsig = self::buildRrsig($now - 7200, $now - 3600);

        static::assertFalse(RRSIGVerifier::isWithinTimeWindow($rrsig, clockSkew: 0));
    }

    public function testAcceptsRecentlyExpiredRrsigWithSkew(): void
    {
        $now = Timestamp::now()->getSeconds();
        $rrsig = self::buildRrsig($now - 7200, $now - 100);

        static::assertTrue(RRSIGVerifier::isWithinTimeWindow($rrsig, clockSkew: 300));
    }

    public function testRejectsNotYetValidRrsigWithoutSkew(): void
    {
        $now = Timestamp::now()->getSeconds();
        $rrsig = self::buildRrsig($now + 3600, $now + 7200);

        static::assertFalse(RRSIGVerifier::isWithinTimeWindow($rrsig, clockSkew: 0));
    }

    public function testAcceptsSlightlyFutureRrsigWithSkew(): void
    {
        $now = Timestamp::now()->getSeconds();
        $rrsig = self::buildRrsig($now + 100, $now + 7200);

        static::assertTrue(RRSIGVerifier::isWithinTimeWindow($rrsig, clockSkew: 300));
    }

    public function testDefaultSkewIs300Seconds(): void
    {
        $now = Timestamp::now()->getSeconds();
        $rrsig = self::buildRrsig($now - 7200, $now - 200);

        static::assertTrue(RRSIGVerifier::isWithinTimeWindow($rrsig));
    }

    public function testExpiredBeyondSkewStillRejected(): void
    {
        $now = Timestamp::now()->getSeconds();
        $rrsig = self::buildRrsig($now - 7200, $now - 400);

        static::assertFalse(RRSIGVerifier::isWithinTimeWindow($rrsig, clockSkew: 300));
    }

    public function testFutureBeyondSkewStillRejected(): void
    {
        $now = Timestamp::now()->getSeconds();
        $rrsig = self::buildRrsig($now + 400, $now + 7200);

        static::assertFalse(RRSIGVerifier::isWithinTimeWindow($rrsig, clockSkew: 300));
    }

    public function testCurrentlyValidRrsigAccepted(): void
    {
        $now = Timestamp::now()->getSeconds();
        $rrsig = self::buildRrsig($now - 3600, $now + 3600);

        static::assertTrue(RRSIGVerifier::isWithinTimeWindow($rrsig, clockSkew: 0));
    }

    private static function buildRrsig(int $inception, int $expiration): RRSIGRecord
    {
        return new RRSIGRecord(
            'example.com',
            Duration::seconds(300),
            RecordType::A,
            Algorithm::RSASHA256,
            2,
            300,
            $expiration,
            $inception,
            12_345,
            'example.com',
            Str\repeat("\x00", 64),
        );
    }
}
