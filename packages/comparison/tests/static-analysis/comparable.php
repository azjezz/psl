<?php

/**
 * @phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
 */

declare(strict_types=1);

namespace Psl\Comparison\Tests\StaticAnalysis;

use Override;
use Psl\Comparison;
use Psl\Comparison\Comparable;
use Psl\Comparison\Order;
use stdClass;

abstract class Size implements Comparable<self>
{
    abstract public function normalizedValue(): int;

    #[Override]
    public function compare(mixed $other): Order
    {
        return Comparison\compare::<int>($this->normalizedValue(), $other->normalizedValue());
    }
}

class Inches extends Size
{
    #[Override]
    public function normalizedValue(): int
    {
        return 1;
    }
}

class Centimeters extends Size
{
    #[Override]
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
    return Comparison\compare::<mixed>($a, $b);
}

function test_mixed(): void
{
    namespace\compare_mixed('a', 1);
    namespace\compare_mixed(new stdClass(), []);
}
