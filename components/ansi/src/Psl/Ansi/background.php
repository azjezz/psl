<?php

declare(strict_types=1);

namespace Psl\Ansi;

/**
 * Returns an SGR sequence for the given background color.
 *
 * @pure
 */
function background(Color\Color $color): ControlSequenceIntroducer
{
    $parameters = match ($color->getKind()) {
        Color\ColorKind::Basic => (string) ($color->getValue() + 10),
        Color\ColorKind::Ansi256 => '48;5;' . $color->getValue(),
        Color\ColorKind::Rgb => '48;2;' . $color->getRed() . ';' . $color->getGreen() . ';' . $color->getBlue(),
    };

    return new ControlSequenceIntroducer($parameters, ControlSequenceIntroducerKind::SelectGraphicRendition);
}
