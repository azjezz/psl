<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Comparison;
use Psl\Vec;

/**
 * @implements Comparison\Comparable<Money>
 * @implements Comparison\Equable<Money>
 */
final readonly class Money implements Comparison\Comparable, Comparison\Equable
{
    public function __construct(
        private int $cents,
        private string $currency,
    ) {}

    /**
     * @param Money $other
     *
     * @throws Comparison\Exception\IncomparableException
     */
    public function compare(mixed $other): Comparison\Order
    {
        if ($this->currency !== $other->currency) {
            throw Comparison\Exception\IncomparableException::fromValues(
                $this,
                $other,
                'Cannot compare different currencies',
            );
        }

        return Comparison\compare::<int>($this->cents, $other->cents);
    }

    /**
     * @param Money $other
     */
    public function equals(mixed $other): bool
    {
        return Comparison\equal::<Money>($this, $other);
    }
}

$prices = [new Money(1000, 'USD'), new Money(500, 'USD'), new Money(750, 'USD')];

$sorted = Vec\sort::<Money>($prices, Comparison\sort(...));

// [Money(500), Money(750), Money(1000)]
