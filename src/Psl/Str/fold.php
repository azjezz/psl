<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Perform case folding on a string.
 *
 * Example:
 *
 *      Str\fold('ẞ')
 *      => Str('ss')
 */
function fold(string $str): string
{
    /** @var array<string, string> $caseFold */
    static $caseFold = [
        'µ' => 'μ',
        'ſ' => 's',
        "\xCD\x85" => 'ι',
        'ς' => 'σ',
        "\xCF\x90" => 'β',
        "\xCF\x91" => 'θ',
        "\xCF\x95" => 'φ',
        "\xCF\x96" => 'π',
        "\xCF\xB0" => 'κ',
        "\xCF\xB1" => 'ρ',
        "\xCF\xB5" => 'ε',
        "\xE1\xBA\x9B" => "\xE1\xB9\xA1",
        "\xE1\xBE\xBE" => 'ι',
        'ẞ' => 'ss',
    ];

    return lowercase(replace_every($str, $caseFold));
}
