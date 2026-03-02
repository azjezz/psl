<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Comparison;

final readonly class Money implements Comparison\Comparable, Comparison\Equable
{
    public function __construct(
        private int $cents,
        private string $currency,
    ) {}

    public function compare(mixed $other): Comparison\Order
    {
        if ($this->currency !== $other->currency) {
            throw Comparison\Exception\IncomparableException::fromValues(
                $this,
                $other,
                'Cannot compare different currencies',
            );
        }

        return Comparison\compare($this->cents, $other->cents);
    }

    public function equals(mixed $other): bool
    {
        return Comparison\equal($this, $other);
    }
}

$a = new Money(500, 'USD');
$b = new Money(1000, 'USD');

Comparison\less($a, $b); // true
Comparison\greater($b, $a); // true
Comparison\equal($a, $a); // true
