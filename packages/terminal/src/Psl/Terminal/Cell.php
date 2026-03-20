<?php

declare(strict_types=1);

namespace Psl\Terminal;

use Psl\Ansi\ControlSequenceIntroducer;

use function count;

/**
 * Represents a single terminal cell containing a grapheme cluster and optional styling.
 *
 * @immutable
 */
final readonly class Cell
{
    /**
     * @param string $grapheme The grapheme cluster displayed in this cell (usually a single character).
     * @param list<ControlSequenceIntroducer> $style ANSI style sequences (foreground, background, bold, etc.)
     */
    public function __construct(
        public string $grapheme = ' ',
        public array $style = [],
    ) {}

    /**
     * Returns true if this cell has the same content and styling as another cell.
     */
    public function equals(self $other): bool
    {
        return $this->grapheme === $other->grapheme && self::styleEqual($this->style, $other->style);
    }

    /**
     * @param list<ControlSequenceIntroducer> $a
     * @param list<ControlSequenceIntroducer> $b
     */
    public static function styleEqual(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }

        foreach ($a as $i => $csi) {
            $other = $b[$i] ?? null;
            if ($other === null || $csi->parameters !== $other->parameters || $csi->kind !== $other->kind) {
                return false;
            }
        }

        return true;
    }
}
