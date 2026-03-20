<?php

declare(strict_types=1);

namespace Psl\Ansi;

/**
 * Applies the given SGR sequences to the text, appending a reset sequence.
 *
 * If no sequences are provided, the text is returned unchanged.
 *
 * @throws Exception\InvalidArgumentException If any sequence is not an SGR sequence.
 *
 * @pure
 */
function apply(string $text, ControlSequenceIntroducer ...$sequences): string
{
    if ($sequences === []) {
        return $text;
    }

    $parameters = '';
    foreach ($sequences as $i => $sequence) {
        if ($sequence->kind !== ControlSequenceIntroducerKind::SelectGraphicRendition) {
            throw new Exception\InvalidArgumentException(
                'Only SelectGraphicRendition sequences can be applied to text, got ' . $sequence->kind->name . '.',
            );
        }

        $parameters .= ($i > 0 ? ';' : '') . $sequence->parameters;
    }

    $merged = new ControlSequenceIntroducer($parameters, ControlSequenceIntroducerKind::SelectGraphicRendition);

    return $merged->toString() . $text . namespace\reset()->toString();
}
