<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\H2\Internal\BDPEstimator;

/**
 * @mago-expect lint:prefer-early-continue
 */
final class BDPEstimatorTest extends TestCase
{
    public function testPingAckWithoutPingSentReturnsNull(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        static::assertNull($estimator->onPingAck(1.0));
    }

    public function testPingSentAndAckWithZeroRttReturnsNull(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $estimator->onPingSent(1.0);

        static::assertNull($estimator->onPingAck(1.0));
    }

    public function testPingSentAndAckWithNegativeRttReturnsNull(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $estimator->onPingSent(2.0);

        static::assertNull($estimator->onPingAck(1.0));
    }

    public function testPingAckReturnsNullWhenNoWindowGrowthNeeded(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $estimator->onPingSent(1.0);

        static::assertNull($estimator->onPingAck(1.1));
    }

    public function testPingAckReturnsWindowGrowthWhenBdpExceedsCurrentWindow(): void
    {
        $estimator = new BDPEstimator(65_535, 16_777_216);

        $estimator->onPingSent(1.0);
        $estimator->recordDataReceived(1, 1_000_000);

        $increment = $estimator->onPingAck(1.1);

        static::assertNotNull($increment);
        static::assertGreaterThan(0, $increment);
    }

    public function testSecondPingAckWithoutNewPingSentReturnsNull(): void
    {
        $estimator = new BDPEstimator(65_535, 16_777_216);

        $estimator->onPingSent(1.0);
        $estimator->recordDataReceived(1, 1_000_000);
        $estimator->onPingAck(1.1);

        static::assertNull($estimator->onPingAck(1.2));
    }

    public function testNewPingSentResetsState(): void
    {
        $estimator = new BDPEstimator(65_535, 16_777_216);

        $estimator->onPingSent(1.0);
        $estimator->recordDataReceived(1, 1_000_000);
        $estimator->onPingAck(1.1);

        $estimator->onPingSent(2.0);

        static::assertNull($estimator->onPingAck(2.001));
    }

    public function testWindowGrowthCappedAtMaxReceiveWindowSize(): void
    {
        $estimator = new BDPEstimator(65_535, 100_000);

        $estimator->onPingSent(1.0);
        $estimator->recordDataReceived(1, 10_000_000);

        $increment = $estimator->onPingAck(1.1);

        if ($increment !== null) {
            static::assertLessThanOrEqual(100_000 - 65_535, $increment);
        }

        $estimator->onPingSent(2.0);
        $estimator->recordDataReceived(1, 100_000_000);

        $secondIncrement = $estimator->onPingAck(2.1);

        static::assertNull($secondIncrement);
    }

    public function testRecordDataReceivedBelowThresholdReturnsNoUpdates(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $updates = $estimator->recordDataReceived(1, 100);

        static::assertSame([], $updates);
    }

    public function testRecordDataReceivedAboveConnectionThreshold(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $updates = $estimator->recordDataReceived(1, 40_000);

        $connectionUpdates = [];
        foreach ($updates as $update) {
            if ($update[0] === 0) {
                $connectionUpdates[] = $update;
            }
        }

        static::assertCount(1, $connectionUpdates);
        static::assertNotNull($connectionUpdates[0]);
        static::assertSame(40_000, $connectionUpdates[0][1]);
    }

    public function testRecordDataReceivedAboveStreamThreshold(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $updates = $estimator->recordDataReceived(3, 40_000);

        $streamUpdates = [];
        foreach ($updates as $update) {
            if ($update[0] === 3) {
                $streamUpdates[] = $update;
            }
        }

        static::assertCount(1, $streamUpdates);
        static::assertNotNull($streamUpdates[0]);
        static::assertSame(40_000, $streamUpdates[0][1]);
    }

    public function testRecordDataReceivedTriggersConnectionAndStreamUpdates(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $updates = $estimator->recordDataReceived(5, 40_000);

        static::assertCount(2, $updates);

        static::assertSame(0, $updates[0][0]);
        static::assertSame(40_000, $updates[0][1]);

        static::assertSame(5, $updates[1][0]);
        static::assertSame(40_000, $updates[1][1]);
    }

    public function testAccumulatedDataAcrossMultipleCallsTriggersUpdate(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $updates1 = $estimator->recordDataReceived(1, 10_000);
        static::assertSame([], $updates1);

        $updates2 = $estimator->recordDataReceived(1, 10_000);
        static::assertSame([], $updates2);

        $updates3 = $estimator->recordDataReceived(1, 15_000);

        $streamUpdates = [];
        foreach ($updates3 as $update) {
            if ($update[0] === 1) {
                $streamUpdates[] = $update;
            }
        }

        static::assertCount(1, $streamUpdates);
        static::assertNotNull($streamUpdates[0]);
        static::assertSame(35_000, $streamUpdates[0][1]);
    }

    public function testRemoveStreamCleansUpTracking(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $estimator->recordDataReceived(7, 10_000);
        $estimator->removeStream(7);

        $updates = $estimator->recordDataReceived(7, 10_000);

        $streamUpdates = [];
        foreach ($updates as $update) {
            if ($update[0] === 7) {
                $streamUpdates[] = $update;
            }
        }

        static::assertSame([], $streamUpdates);
    }

    public function testRemoveNonExistentStreamDoesNotError(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $estimator->removeStream(999);

        static::assertTrue(true);
    }

    public function testMultipleStreamsTrackedIndependently(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $estimator->recordDataReceived(1, 10_000);
        $estimator->recordDataReceived(3, 10_000);

        $updates = $estimator->recordDataReceived(1, 25_000);

        /** @var list<array{non-negative-int, positive-int}> $stream1Updates */
        $stream1Updates = [];
        /** @var list<array{non-negative-int, positive-int}> $stream3Updates */
        $stream3Updates = [];
        foreach ($updates as $update) {
            if ($update[0] === 1) {
                $stream1Updates[] = $update;
            }

            if ($update[0] === 3) {
                $stream3Updates[] = $update;
            }
        }

        static::assertCount(1, $stream1Updates);
        static::assertSame(35_000, $stream1Updates[0][1]);
        static::assertSame([], $stream3Updates);
    }

    public function testEwmaSmoothingAppliedAcrossMultiplePingCycles(): void
    {
        $estimator = new BDPEstimator(65_535, 16_777_216);

        $estimator->onPingSent(1.0);
        $estimator->recordDataReceived(1, 2_000_000);
        $firstIncrement = $estimator->onPingAck(1.1);

        $estimator->onPingSent(2.0);
        $estimator->recordDataReceived(1, 2_000_000);
        $secondIncrement = $estimator->onPingAck(2.1);

        static::assertNotNull($firstIncrement);

        if ($secondIncrement !== null) {
            static::assertGreaterThan(0, $secondIncrement);
        }
    }

    public function testWindowNeverGrowsBelowInitialSize(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $estimator->onPingSent(1.0);
        $estimator->recordDataReceived(1, 1);
        $increment = $estimator->onPingAck(1.1);

        static::assertNull($increment);
    }

    public function testConnectionBytesConsumedResetsAfterThreshold(): void
    {
        $estimator = new BDPEstimator(65_535, 1_048_576);

        $updates1 = $estimator->recordDataReceived(1, 40_000);
        $hasConnectionUpdate = false;
        foreach ($updates1 as $update) {
            if ($update[0] === 0) {
                $hasConnectionUpdate = true;
            }
        }

        static::assertTrue($hasConnectionUpdate);

        $updates2 = $estimator->recordDataReceived(3, 100);
        $hasConnectionUpdate2 = false;
        foreach ($updates2 as $update) {
            if ($update[0] === 0) {
                $hasConnectionUpdate2 = true;
            }
        }

        static::assertFalse($hasConnectionUpdate2);
    }
}
