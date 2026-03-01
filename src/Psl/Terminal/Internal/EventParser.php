<?php

declare(strict_types=1);

namespace Psl\Terminal\Internal;

use Psl\Str;
use Psl\Terminal\Event;

/**
 * Parses raw stdin bytes into terminal Event objects.
 *
 * Handles ANSI escape sequences for keys, mouse (SGR format), and bracketed paste.
 *
 * @internal
 */
final class EventParser
{
    private string $buffer = '';
    private bool $inPaste = false;
    private string $pasteBuffer = '';

    private const string PASTE_START = "\e[200~";
    private const string PASTE_END = "\e[201~";

    /**
     * Feed raw bytes from stdin and return parsed events.
     *
     * @return list<Event\Key|Event\Mouse|Event\Paste|Event\Resize|Event\Focus>
     */
    public function feed(string $data): array
    {
        $this->buffer .= $data;
        $events = [];

        while ($this->buffer !== '') {
            $event = $this->parseNext();
            if ($event === false) {
                break;
            }

            if ($event !== null) {
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * Parse the next event from the buffer.
     *
     * @return Event\Key|Event\Mouse|Event\Paste|Event\Focus|null|false null if byte was skipped, false if incomplete
     */
    private function parseNext(): Event\Key|Event\Mouse|Event\Paste|Event\Focus|null|false
    {
        if ($this->inPaste) {
            return $this->parsePasteContent();
        }

        if (Str\Byte\starts_with($this->buffer, self::PASTE_START)) {
            $this->buffer = Str\Byte\slice($this->buffer, Str\Byte\length(self::PASTE_START));
            $this->inPaste = true;
            return null;
        }

        if (
            Str\Byte\length(self::PASTE_START) > Str\Byte\length($this->buffer)
            && Str\Byte\starts_with(self::PASTE_START, $this->buffer)
        ) {
            return false;
        }

        if ($this->buffer[0] === "\e") {
            return $this->parseEscapeSequence();
        }

        return $this->parseSingleByte();
    }

    private function parsePasteContent(): Event\Paste|null|false
    {
        $endPos = Str\Byte\search($this->buffer, self::PASTE_END);
        if ($endPos === null) {
            $this->pasteBuffer .= $this->buffer;
            $this->buffer = '';
            return false;
        }

        $this->pasteBuffer .= Str\Byte\slice($this->buffer, 0, $endPos);
        $this->buffer = Str\Byte\slice($this->buffer, $endPos + Str\Byte\length(self::PASTE_END));
        $result = new Event\Paste($this->pasteBuffer);
        $this->pasteBuffer = '';
        $this->inPaste = false;
        return $result;
    }

    private function parseSingleByte(): null|Event\Key
    {
        $byte = $this->buffer[0];
        $this->buffer = Str\Byte\slice($this->buffer, 1);
        $ord = ord($byte);

        return match (true) {
            $ord === 0x0D => Event\Key::named('enter'),
            $ord === 0x09 => Event\Key::named('tab'),
            $ord === 0x7F, $ord === 0x08 => Event\Key::named('backspace'),
            $ord >= 0x01 && $ord <= 0x1A => self::controlCharKey($ord),
            $ord === 0x00 => Event\Key::named('ctrl+space'),
            $ord >= 0x20 && $ord <= 0x7E => Event\Key::char($byte),
            $ord >= 0xC0 => $this->parseUtf8($byte),
            default => null,
        };
    }

    private static function controlCharKey(int $ord): Event\Key
    {
        $letter = chr($ord + 0x60);
        return Event\Key::named('ctrl+' . $letter);
    }

    private function parseUtf8(string $firstByte): null|Event\Key
    {
        $ord = ord($firstByte);
        $needed = match (true) {
            ($ord & 0xE0) === 0xC0 => 1,
            ($ord & 0xF0) === 0xE0 => 2,
            ($ord & 0xF8) === 0xF0 => 3,
            default => 0,
        };

        if (Str\Byte\length($this->buffer) < $needed) {
            $this->buffer = $firstByte . $this->buffer;
            return null;
        }

        $char = $firstByte . Str\Byte\slice($this->buffer, 0, $needed);
        $this->buffer = Str\Byte\slice($this->buffer, $needed);

        return Event\Key::char($char);
    }

    /**
     * @return Event\Key|Event\Mouse|Event\Focus|null|false
     */
    private function parseEscapeSequence(): Event\Key|Event\Mouse|Event\Focus|null|false
    {
        if (Str\Byte\length($this->buffer) < 2) {
            if (Str\Byte\length($this->buffer) === 1) {
                $this->buffer = '';
                return Event\Key::named('escape');
            }

            return false;
        }

        $second = $this->buffer[1];

        if ($second === '[') {
            return $this->parseCsiSequence();
        }

        if (ord($second) >= 0x20 && ord($second) <= 0x7E) {
            $this->buffer = Str\Byte\slice($this->buffer, 2);
            return Event\Key::named('alt+' . $second);
        }

        $this->buffer = Str\Byte\slice($this->buffer, 1);
        return Event\Key::named('escape');
    }

    /**
     * @return Event\Key|Event\Mouse|Event\Focus|null|false
     */
    private function parseCsiSequence(): Event\Key|Event\Mouse|Event\Focus|null|false
    {
        $len = Str\Byte\length($this->buffer);

        for ($i = 2; $i < $len; $i++) {
            $c = $this->buffer[$i];

            if ($i === 2 && $c === '<') {
                return $this->parseSgrMouse();
            }

            if ($c >= 'A' && $c <= 'Z' || $c === '~' || $c >= 'a' && $c <= 'z') {
                /** @var non-negative-int $paramLen */
                $paramLen = $i - 2;
                $params = Str\Byte\slice($this->buffer, 2, $paramLen);
                $this->buffer = Str\Byte\slice($this->buffer, $i + 1);

                if ($params === '' && $c === 'I') {
                    return new Event\Focus(true);
                }

                if ($params === '' && $c === 'O') {
                    return new Event\Focus(false);
                }

                return CsiKeyMap::map($params, $c);
            }
        }

        return false;
    }

    /**
     * @return Event\Mouse|null|false
     */
    private function parseSgrMouse(): Event\Mouse|null|false
    {
        $len = Str\Byte\length($this->buffer);

        for ($i = 3; $i < $len; $i++) {
            $c = $this->buffer[$i];
            if ($c === 'M' || $c === 'm') {
                /** @var non-negative-int $paramLen */
                $paramLen = $i - 3;
                $params = Str\Byte\slice($this->buffer, 3, $paramLen);
                $isRelease = $c === 'm';
                $this->buffer = Str\Byte\slice($this->buffer, $i + 1);

                return SgrMouseParser::parse($params, $isRelease);
            }
        }

        return false;
    }
}
