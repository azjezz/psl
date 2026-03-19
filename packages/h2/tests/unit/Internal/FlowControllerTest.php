<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\H2\Exception\FlowControlException;
use Psl\H2\Internal\FlowController;
use Psl\H2\Internal\StreamEntry;

final class FlowControllerTest extends TestCase
{
    public function testInitialConnectionWindow(): void
    {
        $fc = new FlowController();

        static::assertSame(65_535, $fc->connectionSendWindow());
        static::assertSame(65_535, $fc->connectionReceiveWindow());
    }

    public function testConsumeSendWindow(): void
    {
        $fc = new FlowController();
        $stream = new StreamEntry();

        $fc->consumeSendWindow($stream, 1000);

        static::assertSame(64_535, $fc->connectionSendWindow());
        static::assertSame(64_535, $stream->sendWindow);
    }

    public function testConsumeSendWindowExhausted(): void
    {
        $fc = new FlowController();
        $stream = new StreamEntry();

        $this->expectException(FlowControlException::class);
        $fc->consumeSendWindow($stream, 70_000);
    }

    public function testApplyConnectionWindowUpdate(): void
    {
        $fc = new FlowController();

        $fc->applyConnectionWindowUpdate(1000);

        static::assertSame(66_535, $fc->connectionSendWindow());
    }

    public function testApplyConnectionWindowOverflow(): void
    {
        $fc = new FlowController();

        $this->expectException(FlowControlException::class);
        $fc->applyConnectionWindowUpdate(2_147_483_647);
    }

    public function testApplyStreamWindowUpdate(): void
    {
        $fc = new FlowController();
        $stream = new StreamEntry();

        $fc->applyStreamWindowUpdate($stream, 1, 1000);

        static::assertSame(66_535, $stream->sendWindow);
    }

    public function testApplyStreamWindowOverflow(): void
    {
        $fc = new FlowController();
        $stream = new StreamEntry();

        $this->expectException(FlowControlException::class);
        $fc->applyStreamWindowUpdate($stream, 1, 2_147_483_647);
    }

    public function testAvailableSendWindow(): void
    {
        $fc = new FlowController();
        $stream = new StreamEntry();

        static::assertSame(65_535, $fc->availableSendWindow($stream));

        $fc->consumeSendWindow($stream, 60_000);

        static::assertSame(5535, $fc->availableSendWindow($stream));
    }

    public function testConsumeReceiveWindow(): void
    {
        $fc = new FlowController();
        $stream = new StreamEntry();

        $fc->consumeReceiveWindow($stream, 500);

        static::assertSame(65_035, $fc->connectionReceiveWindow());
        static::assertSame(65_035, $stream->receiveWindow);
    }

    public function testCustomInitialWindow(): void
    {
        $fc = new FlowController(32_768);

        static::assertSame(32_768, $fc->connectionSendWindow());
    }
}
