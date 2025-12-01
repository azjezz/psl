# Filterable

The `Filterable` component introduces a uniform approach for classes within PSL to provide filtering capabilities.

This component encapsulates the `Psl\Filterable\FilterableInterface`, establishing a contract that enables implementing
classes to offer a standardized method for filtering their elements based on custom predicates.

## Usage

```php
use Psl\Filterable\FilterableInterface;

/**
 * @implements FilterableInterface<int>
 */
final class NumberCollection implements FilterableInterface
{
    /** @var list<int> */
    private array $numbers;

    public function __construct(int ...$numbers)
    {
        $this->numbers = $numbers;
    }

    public function filter(\Closure $predicate): static
    {
        $filtered = [];
        foreach ($this->numbers as $number) {
            if ($predicate($number)) {
                $filtered[] = $number;
            }
        }

        return new self(...$filtered);
    }
}

// Filter even numbers from a collection
$numbers = new NumberCollection(1, 2, 3, 4, 5, 6);
$evenNumbers = $numbers->filter(fn(int $n): bool => $n % 2 === 0);
```

## API

### Interfaces

---

* [`interface FilterableInterface`](FilterableInterface.php)

    Defines a contract for filtering collections or objects based on a predicate function.

    Classes that implement this interface are expected to provide a filter() method,
  which accepts a predicate (a closure that returns a boolean) and returns a new instance
  containing only the elements that satisfy the predicate condition.

    This pattern is beneficial in scenarios where you need to extract a subset of elements
  from a collection based on custom criteria, while maintaining the same type and structure
  as the original collection. The original instance remains unchanged.

    Implementing the `FilterableInterface` signals that a class supports this standardized mechanism for filtering, ensuring consistency across the PSL.

    ```php
    use Psl\Filterable;

    /**
     * @implements Filterable\FilterableInterface<string>
     */
    final class StringList implements Filterable\FilterableInterface
    {
        /** @var list<string> */
        private array $items;

        public function __construct(string ...$items)
        {
            $this->items = $items;
        }

        public function filter(\Closure $predicate): static
        {
            $filtered = [];
            foreach ($this->items as $item) {
                if ($predicate($item)) {
                    $filtered[] = $item;
                }
            }

            return new self(...$filtered);
        }
    }

    $list = new StringList('apple', 'banana', 'apricot', 'cherry');
    $aFruits = $list->filter(fn(string $s): bool => str_starts_with($s, 'a'));
    ```
