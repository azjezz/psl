<?php

declare(strict_types=1);

namespace Psl\Regex\Tests\StaticAnalysis;

use Psl;
use Psl\Regex;

function take_string(string $_): void {}

/**
 * @throws Regex\Exception\ExceptionInterface
 * @throws Psl\Exception\InvariantViolationException
 */
function test(): void
{
    $subject = 'PHP is the web scripting language of choice.';
    $pattern = '/(php)/i';

    $e = Regex\capture_groups([1]);
    $firstMatch = Regex\first_match::<array>($subject, $pattern, $e);

    Psl\invariant(null !== $firstMatch, 'It matches!');

    namespace\take_string($firstMatch[0]);
    namespace\take_string($firstMatch[1]);
}
