<?php

declare(strict_types=1);

namespace Psl\Ansi;

/**
 * Wraps the given text in an OSC 8 hyperlink, optionally applying SGR styles.
 *
 * @throws Exception\InvalidArgumentException If any style is not an SGR sequence.
 *
 * @pure
 */
function link(string $text, string $url, ControlSequenceIntroducer ...$styles): string
{
    foreach ($styles as $style) {
        if ($style->kind !== ControlSequenceIntroducerKind::SelectGraphicRendition) {
            throw new Exception\InvalidArgumentException('Only SelectGraphicRendition sequences can be applied to text, got '
            . $style->kind->name
            . '.');
        }
    }

    $osc_open = new OperatingSystemCommand(OperatingSystemCommandKind::Hyperlink, ';' . $url);
    $osc_close = new OperatingSystemCommand(OperatingSystemCommandKind::Hyperlink, ';');

    if ($styles === []) {
        return $osc_open->toString() . $text . $osc_close->toString();
    }

    $parameters = '';
    foreach ($styles as $i => $style) {
        $parameters .= ($i > 0 ? ';' : '') . $style->parameters;
    }

    $merged = new ControlSequenceIntroducer($parameters, ControlSequenceIntroducerKind::SelectGraphicRendition);

    return $merged->toString() . $osc_open->toString() . $text . $osc_close->toString() . reset()->toString();
}
