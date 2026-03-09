<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

enum BorderStyle
{
    case Plain;
    case Rounded;
    case Double;
    case Thick;

    /**
     * @return array{string, string, string, string, string, string}
     *  [top-left, top-right, bottom-left, bottom-right, horizontal, vertical]
     */
    public function characters(): array
    {
        return match ($this) {
            self::Plain => ['+', '+', '+', '+', '-', '|'],
            self::Rounded => ["\u{256D}", "\u{256E}", "\u{2570}", "\u{256F}", "\u{2500}", "\u{2502}"],
            self::Double => ["\u{2554}", "\u{2557}", "\u{255A}", "\u{255D}", "\u{2550}", "\u{2551}"],
            self::Thick => ["\u{250F}", "\u{2513}", "\u{2517}", "\u{251B}", "\u{2501}", "\u{2503}"],
        };
    }
}
