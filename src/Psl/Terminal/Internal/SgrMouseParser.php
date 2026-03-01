<?php

declare(strict_types=1);

namespace Psl\Terminal\Internal;

use Psl\Iter;
use Psl\Str;
use Psl\Terminal\Event;

/**
 * Parses SGR mouse protocol parameters into Mouse events.
 *
 * @internal
 */
final class SgrMouseParser
{
    private function __construct() {}

    public static function parse(string $params, bool $isRelease): null|Event\Mouse
    {
        $parts = Str\Byte\split($params, ';');
        if (Iter\count($parts) !== 3) {
            return null;
        }

        $btn = (int) $parts[0];
        $col = (int) $parts[1];
        $row = (int) $parts[2];

        $kind = self::kindFromButton($btn, $isRelease);

        return new Event\Mouse($kind, $col, $row, new Event\MouseModifiers($btn & 0b1_1100));
    }

    private static function kindFromButton(int $btn, bool $isRelease): Event\MouseKind
    {
        if ($isRelease) {
            return Event\MouseKind::Release;
        }

        if (($btn & 0b10_0000) !== 0) {
            // Bit 5 = motion. Bits 0-1 = 0b11 means no button held → Move
            return ($btn & 0b11) === 0b11 ? Event\MouseKind::Move : Event\MouseKind::Drag;
        }

        if (($btn & 0b100_0000) !== 0) {
            return ($btn & 0b1) === 0 ? Event\MouseKind::ScrollUp : Event\MouseKind::ScrollDown;
        }

        return Event\MouseKind::Press;
    }
}
