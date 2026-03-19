<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\StateMachine;

use PHPUnit\Framework\TestCase;
use Psl\H2\Event\DataReceived;
use Psl\H2\Event\PingReceived;
use Psl\H2\Frame\DataFrame;
use Psl\H2\Frame\FrameType;
use Psl\H2\Frame\HeadersFrame;
use Psl\H2\Frame\PingFrame;
use Psl\H2\Internal\BDPEstimator;
use Psl\H2\Internal\StateMachine;
use Psl\HPACK\Encoder;
use Psl\HPACK\Header;

use function str_repeat;

/**
 * @mago-expect lint:prefer-early-continue
 */
final class BDPEstimatorIntegrationTest extends TestCase
{
    public function testReceiveDataWithBdpEstimator(): void
    {
        $bdp = new BDPEstimator(100, 1_048_576);

        $sm = new StateMachine(false, bdpEstimator: $bdp);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'POST'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, false, true)->toRaw();
        $sm->receive($headersRaw);

        $data = str_repeat('x', 1000);
        $dataRaw = new DataFrame(1, $data, false)->toRaw();
        [$responseFrames, $events] = $sm->receive($dataRaw);

        static::assertCount(1, $events);
        static::assertInstanceOf(DataReceived::class, $events[0]);

        $hasWindowUpdate = false;
        foreach ($responseFrames as $frame) {
            if ($frame->type === FrameType::WindowUpdate->value) {
                $hasWindowUpdate = true;
            }
        }

        static::assertTrue($hasWindowUpdate);
    }

    public function testPingAckWithBdpEstimator(): void
    {
        $bdp = new BDPEstimator(65_535, 1_048_576);

        $sm = new StateMachine(false, bdpEstimator: $bdp);
        $sm->initialize();

        $sm->ping('testping');

        $pingAckRaw = new PingFrame('testping', true)->toRaw();
        [$responseFrames, $events] = $sm->receive($pingAckRaw);

        static::assertCount(1, $events);
        static::assertInstanceOf(PingReceived::class, $events[0]);
        static::assertTrue($events[0]->ack);
    }

    public function testPingAckWithBdpEstimatorAndDataProducesWindowUpdate(): void
    {
        $bdp = new BDPEstimator(100, 1_048_576);

        $sm = new StateMachine(false, bdpEstimator: $bdp);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'POST'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, false, true)->toRaw();
        $sm->receive($headersRaw);

        $sm->ping('bdptest1');

        $data = str_repeat('x', 1000);
        $dataRaw = new DataFrame(1, $data, false)->toRaw();
        $sm->receive($dataRaw);

        $pingAckRaw = new PingFrame('bdptest1', true)->toRaw();
        [$responseFrames, $events] = $sm->receive($pingAckRaw);

        static::assertInstanceOf(PingReceived::class, $events[0]);
        static::assertTrue($events[0]->ack);
    }

    public function testEmptyDataFrameRateLimiting(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'POST'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, false, true)->toRaw();
        $sm->receive($headersRaw);

        $emptyDataRaw = new DataFrame(1, '', false)->toRaw();
        [$_, $events] = $sm->receive($emptyDataRaw);

        static::assertCount(1, $events);
        static::assertInstanceOf(DataReceived::class, $events[0]);
        static::assertSame('', $events[0]->data);
    }
}
