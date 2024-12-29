<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Math;

use PHPUnit\Framework\TestCase;
use Psl\Math;

trait FloatAsserts
{
    /**
     * Because not all systems have the same rounding rules and precisions,
     * This method provides an assertion to compare floats based on epsilon:.
     *
     * @see https://www.php.net/manual/en/language.types.float.php#language.types.float.comparison
     */
    public static function assertFloatEquals(float $a, float $b, float $epsilon = PHP_FLOAT_EPSILON): void
    {
        TestCase::assertTrue(
            Math\abs($a - $b) <= $epsilon,
            'Failed asserting that float ' . $a . ' is equal to ' . $b . '.',
        );
    }
}
