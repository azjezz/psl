<?php

declare(strict_types=1);

namespace Psl\H2\Tests\Unit\StateMachine;

use PHPUnit\Framework\TestCase;
use Psl\H2\Exception\ProtocolException;
use Psl\H2\Exception\StreamException;
use Psl\H2\Frame;
use Psl\H2\Frame\FrameType;
use Psl\H2\Frame\HeadersFrame;
use Psl\H2\Frame\PushPromiseFrame;
use Psl\H2\Frame\SettingsFrame;
use Psl\H2\Internal\StateMachine;
use Psl\H2\Setting;
use Psl\HPACK\Encoder;
use Psl\HPACK\Header;

final class SendPushPromiseTest extends TestCase
{
    private function createServerWithOpenStream(): StateMachine
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, false, true)->toRaw();
        $sm->receive($headersRaw);

        return $sm;
    }

    public function testSendPushPromise(): void
    {
        $sm = $this->createServerWithOpenStream();

        $frames = $sm->sendPushPromise(1, 2, [
            new Header(':method', 'GET'),
            new Header(':path', '/pushed'),
        ]);

        static::assertCount(1, $frames);
        static::assertSame(FrameType::PushPromise->value, $frames[0]->type);
    }

    public function testSendPushPromiseOnIdleStreamThrows(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $this->expectException(StreamException::class);

        $sm->sendPushPromise(99, 2, [new Header(':method', 'GET')]);
    }

    public function testSendPushPromiseWhenDisabledThrows(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, false, true)->toRaw();
        $sm->receive($headersRaw);

        $settingsFrame = new SettingsFrame([Setting::EnablePush->value => 0], false)->toRaw();
        $sm->receive($settingsFrame);

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('disabled PUSH');

        $sm->sendPushPromise(1, 2, [new Header(':method', 'GET')]);
    }

    public function testSendPushPromiseWithContinuationFrames(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $settingsFrame = new SettingsFrame([Setting::MaxFrameSize->value => 16_384], false)->toRaw();
        $sm->receive($settingsFrame);

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, false, true)->toRaw();
        $sm->receive($headersRaw);

        $largeHeaders = [
            new Header(':method', 'GET'),
            new Header(':scheme', 'https'),
            new Header(':path', '/pushed'),
        ];
        for ($i = 0; $i < 1000; $i++) {
            $largeHeaders[] = new Header('x-push-' . $i, str_repeat('v', 40));
        }

        $frames = $sm->sendPushPromise(1, 2, $largeHeaders);

        static::assertGreaterThan(1, count($frames));

        $parsed = PushPromiseFrame::fromRaw($frames[0]);
        static::assertFalse($parsed->endHeaders);

        for ($i = 1; $i < (count($frames) - 1); $i++) {
            static::assertSame(0x09, $frames[$i]->type);
        }

        /** @var int<0, max> $lastIndex */
        $lastIndex = count($frames) - 1;
        $lastFrame = $frames[$lastIndex];
        static::assertSame(0x09, $lastFrame->type);
        static::assertSame(0x04, $lastFrame->flags & 0x04);
    }

    public function testSendPushPromiseOnHalfClosedRemote(): void
    {
        $sm = new StateMachine(false);
        $sm->initialize();

        $encoder = new Encoder();
        $block = $encoder->encode([
            new Header(':method', 'GET'),
            new Header(':scheme', 'https'),
            new Header(':path', '/'),
        ]);
        $headersRaw = new HeadersFrame(1, $block, true, true)->toRaw();
        $sm->receive($headersRaw);

        $frames = $sm->sendPushPromise(1, 2, [
            new Header(':method', 'GET'),
            new Header(':path', '/pushed'),
        ]);

        static::assertCount(1, $frames);
    }

    public function testSendHeadersOnReservedLocalStream(): void
    {
        $sm = $this->createServerWithOpenStream();

        $sm->sendPushPromise(1, 2, [
            new Header(':method', 'GET'),
            new Header(':path', '/pushed'),
        ]);

        $result = $sm->sendHeadersEncoded(2, [new Header(':status', '200')], true);

        static::assertNotEmpty($result);
    }
}
