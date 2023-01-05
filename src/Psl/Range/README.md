# Range

The `Range` component provides a way to create a range of integer values.

## Usage

```php
use Psl\Range;

$range = Range\from(0);                 // 0.. ( from range )
$range = Range\to(10);                  // ..10 ( to range )
$range = Range\to(10, true);            // ..=10 ( to range, inclusive )
$range = Range\between(0, 10);          // 0..10 ( between range )
$range = Range\between(0, 10, true);    // 0..=10 ( between range, inclusive )
$range = Range\full();                  // .. (full range)
```

## API

### Functions

---

* [`between(int $lower_bound, int $upper_bound, bool $upper_inclusive = false): BetweenRange`](between.php)

    Create a `BetweenRange` from `$lower_bound`, to `$upper_bound`.

    If `$upper_inclusive` is `true`, the upper bound is included in the range.

    ---

    ```php
    use Psl\Range;

    $range = Range\between(0, 10);
    foreach ($range as $value) {
        // $value is 1, 2, 3, 4, 5, 6, 7, 8, 9
    }

    $range = Range\between(0, 10, true);
    foreach ($range as $value) {
        // $value is 1, 2, 3, 4, 5, 6, 7, 8, 9, 10
    }
  
    $range = Range\between(10, 0);
    if ($range->contains(5)) {
        // $range contains 5
    }
    ```

---

* [`from(int $lower_bound): FromRange`](from.php)

    Create a `FromRange` from `$lower_bound`.

    ---

    ```php
    use Psl\Range;

    $range = Range\from(0);
    foreach ($range as $value) {
        // $value is 1, 2, 3, 4, 5, 6, 7, 8, 9, ...
    }
    ```

---

* [`to(int $upper_bound, bool $upper_inclusive = false): ToRange`](to.php)

    Create a `ToRange` to `$upper_bound`.

    If `$upper_inclusive` is `true`, the upper bound is included in the range.

    ---

    ```php
    use Psl\Range;

    $range = Range\to(10);
    $range = Range\to(10, true);
    ```

---

* [`full(): FullRange`](full.php)

    Create a `FullRange`.

    ---

    ```php
    use Psl\Range;

    $range = Range\full();
    ```

### Classes

---

* [`final class BetweenRange implements LowerBoundRangeInterface, UpperBoundRangeInterface`](BetweenRange.php)

    A range between two integer values.

    ```php
    use Psl\Range;

    $range = new Range\BetweenRange(0, 10);
  
    $range->contains(5); // true
    $range->contains(100); // false
    $range->getLowerBound(); // 0
    $range->getUpperBound(); // 10
    $range->isUpperInclusive(); // false
    ```

---

* [`final class FromRange implements LowerBoundRangeInterface`](FromRange.php)

    A range from an integer value.

    ```php
    use Psl\Range;

    $range = new Range\FromRange(0);
  
    $range->contains(5); // true
    $range->contains(100); // true
    $range->getLowerBound(); // 0
    ```

---

* [`final class ToRange implements UpperBoundRangeInterface`](ToRange.php)

    A range to an integer value.

    ```php
    use Psl\Range;

    $range = new Range\ToRange(10);
  
    $range->contains(5); // true
    $range->contains(100); // false
    $range->getUpperBound(); // 10
    $range->isUpperInclusive(); // false
    ```

---

* [`final class FullRange implements RangeInterface`](FullRange.php)

    A range of all integer values.

    ```php
    use Psl\Range;

    $range = new Range\FullRange();
  
    $range->contains(5); // true
    $range->contains(100); // true
    ```

### Interfaces

---

* [`interface RangeInterface`](RangeInterface.php)

    A set of values that are contained in the range.

    ```php
    use Psl\Range;
    use Psl;

    /**
     * @pure
     */
    function example(Range\RangeInterface $range): void {
        // Check if a value is contained in `$range`.
        if ($range->contains(5)) {
            // $range contains 5
        }
  
        // Combine `$range` with the lower bound of `10`.
        $from = $range->withLowerBound(10);
        Psl\invariant($from instanceof Range\LowerBoundRangeInterface, 'Expected $from to be an instance of LowerBoundRangeInterface.');

        // Combine `$range` with the upper bound of `10`, inclusive.
        $to_inclusive = $range->withUpperBoundInclusive(10);
        Psl\invariant($to_inclusive instanceof Range\UpperBoundRangeInterface, 'Expected $to_inclusive to be an instance of UpperBoundRangeInterface.');
        Psl\invariant($to_inclusive->isUpperInclusive(), 'Expected $to_inclusive to be inclusive.');

        // Combine `$range` with the upper bound of `10`, exclusive.
        $to_exclusive = $range->withUpperBoundExclusive(10);
        Psl\invariant($to_exclusive instanceof Range\UpperBoundRangeInterface, 'Expected $to_exclusive to be an instance of UpperBoundRangeInterface.');
        Psl\invariant(!$to_exclusive->isUpperInclusive(), 'Expected $to_exclusive to be exclusive.');
    }
    ```
---

* [`interface LowerBoundRangeInterface extends RangeInterface`](LowerBoundRangeInterface.php)

    A set of values that are contained in the range, and have a lower bound.

    ```php
    use Psl\Range;
    use Psl;

    /**
     * @pure
     */
    function example(Range\LowerBoundRangeInterface $from): void {
        // Check if a value is contained in `$from`.
        if ($from->contains(5)) {
            // $from contains 5
        }

        // Get the lower bound of `$from`.
        $lower_bound = $from->getLowerBound();

        // Remove the lower bound from `$from`.
        $range = $from->withoutLowerBound();
        Psl\invariant(!$range instanceof Range\LowerBoundRangeInterface, 'Expected $range to not be an instance of LowerBoundRangeInterface.');

        // Combine `$from` with the upper bound of `10`, inclusive.
        $between_inclusive = $from->withUpperBoundInclusive(10);
        Psl\invariant($between_inclusive instanceof Range\LowerBoundRangeInterface, 'Expected $between_inclusive to be an instance of LowerBoundRangeInterface.');
        Psl\invariant($between_inclusive instanceof Range\UpperBoundRangeInterface, 'Expected $between_inclusive to be an instance of UpperBoundRangeInterface.');
        Psl\invariant($between_inclusive->isUpperInclusive(), 'Expected $between_inclusive to be inclusive.');

        // Combine `$from` with the upper bound of `10`, exclusive.
        $between_exclusive = $from->withUpperBoundExclusive(10);
        Psl\invariant($between_exclusive instanceof Range\LowerBoundRangeInterface, 'Expected $between_exclusive to be an instance of LowerBoundRangeInterface.');
        Psl\invariant($between_exclusive instanceof Range\UpperBoundRangeInterface, 'Expected $between_exclusive to be an instance of UpperBoundRangeInterface.');
        Psl\invariant(!$between_exclusive->isUpperInclusive(), 'Expected $between_exclusive to be exclusive.');
  
        // Get iterator for `$from`.
        $iterator = $from->getIterator();
    }
    ```

---

* [`interface UpperBoundRangeInterface extends RangeInterface`](UpperBoundRangeInterface.php)

    A set of values that are contained in the range, and have an upper bound.

    ```php
    use Psl\Range;
    use Psl;

    /**
     * @pure
     */
    function example(Range\UpperBoundRangeInterface $to): void {
        // Check if a value is contained in `$to`.
        if ($to->contains(5)) {
            // $to contains 5
        }

        // Get the upper bound of `$to`.
        $upper_bound = $to->getUpperBound();
  
        // Check if the upper bound of `$to` is inclusive.
        $is_inclusive = $to->isUpperInclusive();
  
        // Remove inclusivity from the upper bound of `$to`.
        $to_exclusive = $to->withUpperInclusive(false);
        Psl\invariant(!$to_exclusive->isUpperInclusive(), 'Expected $to_exclusive to be exclusive.');
  
        // Add inclusivity to the upper bound of `$to`.
        $to_inclusive = $to->withUpperInclusive(true);
        Psl\invariant($to_inclusive->isUpperInclusive(), 'Expected $to_inclusive to be inclusive.');

        // Remove the upper bound from `$to`.
        $range = $to->withoutUpperBound();
        Psl\invariant(!$range instanceof Range\UpperBoundRangeInterface, 'Expected $range to not be an instance of UpperBoundRangeInterface.');

        // Combine `$to` with the lower bound of `10`.
        $from = $to->withLowerBound(10);
        Psl\invariant($from instanceof Range\LowerBoundRangeInterface, 'Expected $from to be an instance of LowerBoundRangeInterface.');
        Psl\invariant($from instanceof Range\UpperBoundRangeInterface, 'Expected $from to be an instance of UpperBoundRangeInterface.');
    }
    ```

### Exceptions

---

* [`final class Exception\InvalidRangeException extends Psl\Exception\InvalidArgumentException implements ExceptionInterface`](Exception/InvalidRangeException.php)

    Exception thrown when an invalid range is encountered.

    ```php
    use Psl\Range;
    use Psl\Range\Exception\InvalidRangeException;

    try {
        $range = new Range\Range(10, 5);
    } catch (InvalidRangeException $e) {
        Psl\invariant($e->getLowerBound() === 10, 'Expected $e->getLowerBound() to be 10.');
        Psl\invariant($e->getUpperBound() === 5, 'Expected $e->getUpperBound() to be 5.');
    }
    ```

---

* [`final class Exception\OverflowException extends Psl\Exception\RuntimeException implements ExceptionInterface`](Exception/OverflowException.php)

    Exception thrown when an overflow occurs.

    ```php
    use Psl\Range;
    use Psl\Range\Exception\OverflowException;
    use Psl\Math;
    
    $range = Range\from(Math\INT64_MAX);
    try {
      foreach ($range as $value) {
        // ...
      }
    } catch(OverflowException $e) {
      // ...
    }
    ```
