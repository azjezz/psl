<?php

declare(strict_types=1);

namespace Psl\Ansi;

/**
 * Returns an SGR sequence for the given foreground color.
 *
 * @pure
 */
function foreground(Color\Color $color): ControlSequenceIntroducer
{
    $parameters = match ($color->getKind()) {
        Color\ColorKind::Basic => (string) $color->getValue(),
        Color\ColorKind::Ansi256 => '38;5;' . $color->getValue(),
        Color\ColorKind::Rgb => '38;2;' . $color->getRed() . ';' . $color->getGreen() . ';' . $color->getBlue(),
    };

    return new ControlSequenceIntroducer($parameters, ControlSequenceIntroducerKind::SelectGraphicRendition);
}
