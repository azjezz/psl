<?php

namespace Psl\Tests\StaticAnalysis\Regex;

use Psl;
use Psl\Regex;

function take_string(string $_foo): void {}

(static function (): void {
    $subject = 'PHP is the web scripting language of choice.';
    $pattern = '/(php)/i';

    $e = Regex\capture_groups([1]);
    $first_match = Regex\first_match($subject, $pattern, $e);

    Psl\invariant($first_match !== null, 'It matches!');

    take_string($first_match[0]);
    take_string($first_match[1]);
})();
