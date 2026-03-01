<?php

declare(strict_types=1);

namespace Psl\Terminal;

use Psl\Ansi\Color\Color;
use Psl\Ansi\ControlSequenceIntroducer;

/**
 * Represents a single terminal cell containing a grapheme cluster and optional styling.
 *
 * @immutable
 */
final readonly class Cell
{
    /**
     * @param string $grapheme The grapheme cluster displayed in this cell (usually a single character).
     * @param Color|null $foreground The foreground color, or null for terminal default.
     * @param Color|null $background The background color, or null for terminal default.
     * @param list<ControlSequenceIntroducer> $modifiers Style modifiers (bold, italic, etc.) as SGR sequences.
     */
    public function __construct(
        public string $grapheme = ' ',
        public null|Color $foreground = null,
        public null|Color $background = null,
        public array $modifiers = [],
    ) {}

    /**
     * Returns true if this cell has the same content and styling as another cell.
     */
    public function equals(Cell $other): bool
    {
        return (
            $this->grapheme === $other->grapheme
            && self::colorsEqual($this->foreground, $other->foreground)
            && self::colorsEqual($this->background, $other->background)
            && self::modifiersEqual($this->modifiers, $other->modifiers)
        );
    }

    private static function colorsEqual(null|Color $a, null|Color $b): bool
    {
        if ($a === null || $b === null) {
            return $a === $b;
        }

        return $a->equals($b);
    }

    /**
     * @param list<ControlSequenceIntroducer> $a
     * @param list<ControlSequenceIntroducer> $b
     */
    private static function modifiersEqual(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }

        foreach ($a as $i => $modifier) {
            $other = $b[$i] ?? null;
            if ($other === null || $modifier->parameters !== $other->parameters || $modifier->kind !== $other->kind) {
                return false;
            }
        }

        return true;
    }
}
