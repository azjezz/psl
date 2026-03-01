<?php

declare(strict_types=1);

namespace Psl\Terminal\Event;

use Psl\Str;

/**
 * Represents a keyboard event.
 *
 * @immutable
 */
final readonly class Key
{
    /**
     * @param string|null $char The printable character, or null for non-printable keys.
     * @param string $name The key name (e.g., 'enter', 'backspace', 'ctrl+c', 'a', 'A').
     */
    private function __construct(
        public null|string $char,
        public string $name,
    ) {}

    /**
     * Check if this key event matches the given key name.
     *
     * Supports: named keys (enter, backspace, tab, escape, space, delete),
     * arrow keys (up, down, left, right), modifier combos (ctrl+c, ctrl+up),
     * function keys (f1-f12), and page keys (page_up, page_down, home, end).
     */
    public function is(string $key): bool
    {
        return Str\lowercase($this->name) === Str\lowercase($key);
    }

    /**
     * Create a key event for a printable character.
     */
    public static function char(string $char): self
    {
        return new self($char, $char);
    }

    /**
     * Create a key event for a named key (non-printable).
     */
    public static function named(string $name): self
    {
        return new self(null, $name);
    }
}
