<?php

/**
 * @phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
 */

declare(strict_types=1);

namespace Psl\Tests\StaticAnalysis\Comparison;

use Psl\Comparison;
use Psl\Comparison\Comparable;
use Psl\Comparison\Order;
use stdClass;

/**
 * @implements Comparable<Size>
 */
abstract class Size implements Comparable
{
    abstract public function normalizedValue(): int;

    #[\Override]
    public function compare(mixed $other): Order
    {
        return Comparison\compare($this->normalizedValue(), $other->normalizedValue());
    }
}

class Inches extends Size
{
    public function normalizedValue(): int
    {
        return 1;
    }
}

class Centimeters extends Size
{
    public function normalizedValue(): int
    {
        return 2;
    }
}

function test_covariant_limitations(): Order
{
    $cm = new Centimeters();
    $inch = new Inches();

    return $cm->compare($inch);
}

function compare_mixed(mixed $a, mixed $b): Order
{
    return Comparison\compare($a, $b);
}

function test_mixed(): void
{
    compare_mixed('a', 1);
    compare_mixed(new stdClass(), []);
}
