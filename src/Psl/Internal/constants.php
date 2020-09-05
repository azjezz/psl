<?php

declare(strict_types=1);

namespace Psl\Internal;

const ALPHABET_BASE64     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
const ALPHABET_BASE64_URL = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
const CASE_FOLD           = [
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
