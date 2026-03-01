<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Event;
use Psl\Terminal\Internal\EventParser;

final class EventParserTest extends TestCase
{
    private EventParser $parser;

    protected function setUp(): void
    {
        $this->parser = new EventParser();
    }

    public function testPrintableCharacter(): void
    {
        $events = $this->parser->feed('a');

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertSame('a', $events[0]->char);
        static::assertSame('a', $events[0]->name);
    }

    public function testEnterKey(): void
    {
        $events = $this->parser->feed("\r");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertTrue($events[0]->is('enter'));
    }

    public function testTabKey(): void
    {
        $events = $this->parser->feed("\t");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertTrue($events[0]->is('tab'));
    }

    public function testBackspaceKey(): void
    {
        $events = $this->parser->feed("\x7F");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertTrue($events[0]->is('backspace'));
    }

    public function testCtrlC(): void
    {
        $events = $this->parser->feed("\x03");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertTrue($events[0]->is('ctrl+c'));
    }

    public function testCtrlU(): void
    {
        $events = $this->parser->feed("\x15");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertTrue($events[0]->is('ctrl+u'));
    }

    public function testCtrlW(): void
    {
        $events = $this->parser->feed("\x17");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertTrue($events[0]->is('ctrl+w'));
    }

    public function testArrowKeys(): void
    {
        $events = $this->parser->feed("\e[A");
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertTrue($events[0]->is('up'));

        $events = $this->parser->feed("\e[B");
        static::assertCount(1, $events);
        static::assertTrue($events[0]->is('down'));

        $events = $this->parser->feed("\e[C");
        static::assertCount(1, $events);
        static::assertTrue($events[0]->is('right'));

        $events = $this->parser->feed("\e[D");
        static::assertCount(1, $events);
        static::assertTrue($events[0]->is('left'));
    }

    public function testCtrlArrowKeys(): void
    {
        $events = $this->parser->feed("\e[1;5A");
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertTrue($events[0]->is('ctrl+up'));

        $events = $this->parser->feed("\e[1;5B");
        static::assertCount(1, $events);
        static::assertTrue($events[0]->is('ctrl+down'));
    }

    public function testPageKeys(): void
    {
        $events = $this->parser->feed("\e[5~");
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertTrue($events[0]->is('page_up'));

        $events = $this->parser->feed("\e[6~");
        static::assertCount(1, $events);
        static::assertTrue($events[0]->is('page_down'));
    }

    public function testHomeEndKeys(): void
    {
        $events = $this->parser->feed("\e[H");
        static::assertCount(1, $events);
        static::assertTrue($events[0]->is('home'));

        $events = $this->parser->feed("\e[F");
        static::assertCount(1, $events);
        static::assertTrue($events[0]->is('end'));
    }

    public function testDeleteKey(): void
    {
        $events = $this->parser->feed("\e[3~");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertTrue($events[0]->is('delete'));
    }

    public function testEscapeKeyAmbiguous(): void
    {
        $events = $this->parser->feed("\e");
        static::assertCount(0, $events);

        $events = $this->parser->feed("\e");
        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertTrue($events[0]->is('escape'));
    }

    public function testBracketedPaste(): void
    {
        $events = $this->parser->feed("\e[200~Hello World\e[201~");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Paste::class, $events[0]);
        static::assertSame('Hello World', $events[0]->text);
    }

    public function testSgrMousePress(): void
    {
        $events = $this->parser->feed("\e[<0;10;5M");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Mouse::class, $events[0]);
        static::assertSame(Event\MouseKind::Press, $events[0]->kind);
        static::assertSame(10, $events[0]->column);
        static::assertSame(5, $events[0]->row);
    }

    public function testSgrMouseRelease(): void
    {
        $events = $this->parser->feed("\e[<0;10;5m");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Mouse::class, $events[0]);
        static::assertSame(Event\MouseKind::Release, $events[0]->kind);
    }

    public function testSgrMouseScrollUp(): void
    {
        $events = $this->parser->feed("\e[<64;5;5M");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Mouse::class, $events[0]);
        static::assertSame(Event\MouseKind::ScrollUp, $events[0]->kind);
    }

    public function testSgrMouseScrollDown(): void
    {
        $events = $this->parser->feed("\e[<65;5;5M");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Mouse::class, $events[0]);
        static::assertSame(Event\MouseKind::ScrollDown, $events[0]->kind);
    }

    public function testUtf8MultiByte(): void
    {
        $events = $this->parser->feed("\xC3\xA9");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Key::class, $events[0]);
        static::assertSame('é', $events[0]->char);
    }

    public function testMultipleEventsInOneFeed(): void
    {
        $events = $this->parser->feed('abc');

        static::assertCount(3, $events);
        static::assertSame('a', $events[0]->char);
        static::assertSame('b', $events[1]->char);
        static::assertSame('c', $events[2]->char);
    }

    public function testFocusGained(): void
    {
        $events = $this->parser->feed("\e[I");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Focus::class, $events[0]);
        static::assertTrue($events[0]->focused);
    }

    public function testFocusLost(): void
    {
        $events = $this->parser->feed("\e[O");

        static::assertCount(1, $events);
        static::assertInstanceOf(Event\Focus::class, $events[0]);
        static::assertFalse($events[0]->focused);
    }

    public function testIncrementalFeeding(): void
    {
        $events = $this->parser->feed("\e[");
        static::assertSame([], $events);

        $events = $this->parser->feed('A');
        static::assertCount(1, $events);
        static::assertTrue($events[0]->is('up'));
    }
}
