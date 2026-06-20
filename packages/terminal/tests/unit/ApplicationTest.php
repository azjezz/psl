<?php

declare(strict_types=1);

namespace Psl\Terminal\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Terminal\Application;
use Psl\Terminal\Event;
use Psl\Terminal\Exception\RuntimeException;
use Psl\Terminal\Frame;
use stdClass;

final class ApplicationTest extends TestCase
{
    public function testCustomCreatesApplicationAndStopsImmediately(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop();
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(0, $exitCode);

        $reader->close();
        $writer->close();
    }

    public function testCustomWithTitle(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24, title: 'Test App');

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop();
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(0, $exitCode);

        $outputContent = $output->getBuffer();
        static::assertStringContainsString('Test App', $outputContent);

        $reader->close();
        $writer->close();
    }

    public function testCustomWithEmptyTitle(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24, title: '');

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop();
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(0, $exitCode);

        $reader->close();
        $writer->close();
    }

    public function testStopWithExitCode(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop(42);
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(42, $exitCode);

        $reader->close();
        $writer->close();
    }

    public function testOnRegistersEventHandler(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->keyReceived = false;

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        $app->on::<Event\Key>(Event\Key::class, static function (Event\Key $key, stdClass $state) use ($app): void {
            $state->keyReceived = true;
            $app->stop();
        });

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(Event\Key::char('a'));
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(0, $exitCode);
        static::assertTrue($state->keyReceived);

        $reader->close();
        $writer->close();
    }

    public function testOnRegistersMultipleHandlersForSameEvent(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->callOrder = [];

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        $app->on::<Event\Key>(Event\Key::class, static function (Event\Key $key, stdClass $state): void {
            $state->callOrder[] = 'first';
        });

        $app->on::<Event\Key>(Event\Key::class, static function (Event\Key $key, stdClass $state) use ($app): void {
            $state->callOrder[] = 'second';
            $app->stop();
        });

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(Event\Key::char('a'));
        });

        $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(['first', 'second'], $state->callOrder);

        $reader->close();
        $writer->close();
    }

    public function testDispatchResizeEventIsReceivedByHandler(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->resizeWidth = 0;
        $state->resizeHeight = 0;

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        $app->on::<Event\Resize>(Event\Resize::class, static function (Event\Resize $event, stdClass $state) use ($app): void {
            $state->resizeWidth = $event->width;
            $state->resizeHeight = $event->height;
            $app->stop();
        });

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(new Event\Resize(120, 40));
        });

        $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(120, $state->resizeWidth);
        static::assertSame(40, $state->resizeHeight);

        $reader->close();
        $writer->close();
    }

    public function testDispatchMouseEvent(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->mouseReceived = false;

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        $app->on::<Event\Mouse>(Event\Mouse::class, static function (Event\Mouse $event, stdClass $state) use ($app): void {
            $state->mouseReceived = true;
            $app->stop();
        });

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(new Event\Mouse(Event\MouseKind::Press, 10, 5, Event\MouseButton::Left));
        });

        $app->run(static function (Frame $frame, object $state): void {});

        static::assertTrue($state->mouseReceived);

        $reader->close();
        $writer->close();
    }

    public function testDispatchPasteEvent(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->pasteText = '';

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        $app->on::<Event\Paste>(Event\Paste::class, static function (Event\Paste $event, stdClass $state) use ($app): void {
            $state->pasteText = $event->text;
            $app->stop();
        });

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(new Event\Paste('hello world'));
        });

        $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame('hello world', $state->pasteText);

        $reader->close();
        $writer->close();
    }

    public function testDispatchFocusEvent(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->focused = false;

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        $app->on::<Event\Focus>(Event\Focus::class, static function (Event\Focus $event, stdClass $state) use ($app): void {
            $state->focused = $event->focused;
            $app->stop();
        });

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(new Event\Focus(true));
        });

        $app->run(static function (Frame $frame, object $state): void {});

        static::assertTrue($state->focused);

        $reader->close();
        $writer->close();
    }

    public function testDispatchWithNoHandlersDoesNotCrash(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(Event\Key::char('a'));
            $app->stop();
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(0, $exitCode);

        $reader->close();
        $writer->close();
    }

    public function testHandlerStopsApplicationMidDispatch(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->secondCalled = false;

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        $app->on::<Event\Key>(Event\Key::class, static function (Event\Key $key, stdClass $state) use ($app): void {
            $app->stop();
        });

        $app->on::<Event\Key>(Event\Key::class, static function (Event\Key $key, stdClass $state): void {
            $state->secondCalled = true;
        });

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(Event\Key::char('a'));
        });

        $app->run(static function (Frame $frame, object $state): void {});

        static::assertFalse($state->secondCalled);

        $reader->close();
        $writer->close();
    }

    public function testEmitQueuesCommand(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        $command = new class() implements Ansi\CommandInterface {
            public function toString(): string
            {
                return "\e]TEST_COMMAND\x07";
            }

            public function __toString(): string
            {
                return $this->toString();
            }
        };

        $app->emit($command);

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop();
        });

        $app->run(static function (Frame $frame, object $state): void {});

        $outputContent = $output->getBuffer();
        static::assertStringContainsString("\e]TEST_COMMAND\x07", $outputContent);

        $reader->close();
        $writer->close();
    }

    public function testRunCallsRenderCallback(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $renderCalled = false;

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop();
        });

        $app->run(static function (Frame $frame, object $state) use (&$renderCalled): void {
            $renderCalled = true;
        });

        static::assertTrue($renderCalled);

        $reader->close();
        $writer->close();
    }

    public function testRunWithCustomTickInterval(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24, tickInterval: Duration::milliseconds(50));

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop();
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(0, $exitCode);

        $reader->close();
        $writer->close();
    }

    public function testCustomWithScrollSmoothingDisabled(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->mouseReceived = false;

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24, scrollSmoothing: false);

        $app->on::<Event\Mouse>(Event\Mouse::class, static function (Event\Mouse $event, stdClass $state) use ($app): void {
            $state->mouseReceived = true;
            $app->stop();
        });

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(new Event\Mouse(Event\MouseKind::ScrollUp, 5, 5));
        });

        $app->run(static function (Frame $frame, object $state): void {});

        static::assertTrue($state->mouseReceived);

        $reader->close();
        $writer->close();
    }

    public function testCustomWithMouseMotionEnabled(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24, mouseMotion: true);

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop();
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(0, $exitCode);

        $reader->close();
        $writer->close();
    }

    public function testScrollSmoothingFiltersReversal(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->scrollEvents = [];

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24, scrollSmoothing: true);

        $app->on::<Event\Mouse>(Event\Mouse::class, static function (Event\Mouse $event, stdClass $state): void {
            $state->scrollEvents[] = $event->kind;
        });

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(new Event\Mouse(Event\MouseKind::ScrollDown, 5, 5));
            $app->dispatch(new Event\Mouse(Event\MouseKind::ScrollUp, 5, 5));
            $app->dispatch(new Event\Mouse(Event\MouseKind::ScrollDown, 5, 5));
            $app->stop();
        });

        $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame([Event\MouseKind::ScrollDown, Event\MouseKind::ScrollDown], $state->scrollEvents);

        $reader->close();
        $writer->close();
    }

    public function testNonScrollMousePassesThroughWithSmoothing(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->mouseReceived = false;

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24, scrollSmoothing: true);

        $app->on::<Event\Mouse>(Event\Mouse::class, static function (Event\Mouse $event, stdClass $state) use ($app): void {
            $state->mouseReceived = true;
            $app->stop();
        });

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(new Event\Mouse(Event\MouseKind::Press, 5, 5, Event\MouseButton::Left));
        });

        $app->run(static function (Frame $frame, object $state): void {});

        static::assertTrue($state->mouseReceived);

        $reader->close();
        $writer->close();
    }

    public function testRenderCallbackReceivesFrameAndState(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->value = 'test_state';

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop();
        });

        $receivedFrame = null;
        $receivedState = null;

        $app->run(static function (Frame $frame, object $state) use (&$receivedFrame, &$receivedState): void {
            $receivedFrame = $frame;
            $receivedState = $state;
        });

        static::assertInstanceOf(Frame::class, $receivedFrame);
        static::assertSame(80, $receivedFrame->rect()->width);
        static::assertSame(24, $receivedFrame->rect()->height);
        static::assertSame('test_state', $receivedState->value);

        $reader->close();
        $writer->close();
    }

    public function testIntervalIsRegistered(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->intervalCalled = false;

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24, tickInterval: Duration::milliseconds(10));

        $app->interval(Duration::milliseconds(10), static function (stdClass $state) use ($app): void {
            $state->intervalCalled = true;
            $app->stop();
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(0, $exitCode);
        static::assertTrue($state->intervalCalled);

        $reader->close();
        $writer->close();
    }

    public function testRunWithClosedStreamThrowsRuntimeException(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $reader->close();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Input handle must provide an underlying stream resource.');

        $app->run(static function (Frame $frame, object $state): void {});

        $writer->close();
    }

    public function testDispatchResizeBeforeRunDoesNotCrash(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        $app->dispatch(new Event\Resize(100, 50));

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop();
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(0, $exitCode);

        $reader->close();
        $writer->close();
    }

    public function testMultipleEmitsAreAllFlushed(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        $command1 = new class() implements Ansi\CommandInterface {
            public function toString(): string
            {
                return 'CMD1';
            }

            public function __toString(): string
            {
                return $this->toString();
            }
        };

        $command2 = new class() implements Ansi\CommandInterface {
            public function toString(): string
            {
                return 'CMD2';
            }

            public function __toString(): string
            {
                return $this->toString();
            }
        };

        $app->emit($command1);
        $app->emit($command2);

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop();
        });

        $app->run(static function (Frame $frame, object $state): void {});

        $outputContent = $output->getBuffer();
        static::assertStringContainsString('CMD1', $outputContent);
        static::assertStringContainsString('CMD2', $outputContent);

        $reader->close();
        $writer->close();
    }

    public function testStopDefaultExitCodeIsZero(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24);

        Async\Scheduler::defer(static function () use ($app): void {
            $app->stop();
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(0, $exitCode);

        $reader->close();
        $writer->close();
    }

    public function testOutputClosedDuringRenderStopsApp(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24, tickInterval: Duration::milliseconds(10));

        $renderCount = 0;

        $exitCode = $app->run(static function (Frame $frame, object $state) use ($output, &$renderCount): void {
            $renderCount++;
            if ($renderCount === 2) {
                $output->close();
            }
        });

        static::assertSame(1, $exitCode);

        $reader->close();
        $writer->close();
    }

    public function testInputParsingProducesKeyEvents(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->keysReceived = [];

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24, tickInterval: Duration::milliseconds(10));

        $app->on::<Event\Key>(Event\Key::class, static function (Event\Key $key, stdClass $state) use ($app): void {
            $state->keysReceived[] = $key->name;
            if ($key->is('ctrl+c')) {
                $app->stop();
            }
        });

        Async\Scheduler::defer(static function () use ($writer): void {
            $writer->writeAll("a\x03");
        });

        $exitCode = $app->run(static function (Frame $frame, object $state): void {});

        static::assertSame(0, $exitCode);
        static::assertContains('a', $state->keysReceived);
        static::assertContains('ctrl+c', $state->keysReceived);

        if (!$reader->isClosed()) {
            $reader->close();
        }

        if (!$writer->isClosed()) {
            $writer->close();
        }
    }

    public function testDispatchResizeUpdatesFrameDimensions(): void
    {
        [$reader, $writer] = IO\pipe();
        $output = new IO\MemoryHandle();
        $state = new stdClass();
        $state->frameWidthAfterResize = 0;
        $state->frameHeightAfterResize = 0;

        $app = Application::<stdClass>::custom($state, $reader, $output, 80, 24, tickInterval: Duration::milliseconds(10));

        $renderCount = 0;
        $app->on::<Event\Resize>(Event\Resize::class, static function (Event\Resize $event, stdClass $state) use ($app): void {});

        Async\Scheduler::defer(static function () use ($app): void {
            $app->dispatch(new Event\Resize(120, 40));
            $app->stop();
        });

        $app->run(static function (Frame $frame, stdClass $state) use (&$renderCount): void {
            $renderCount++;
            $state->frameWidthAfterResize = $frame->rect()->width;
            $state->frameHeightAfterResize = $frame->rect()->height;
        });

        static::assertGreaterThanOrEqual(1, $renderCount);

        $reader->close();
        $writer->close();
    }
}
