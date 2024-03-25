# Default

The `Default` component introduces a uniform approach for classes within PSL to provide and utilize default instances.

This component encapsulates the `Psl\Default\DefaultInterface`, establishing a contract that enables implementing
classes to offer a standardized method for instantiating themselves with default values or settings.

## Usage

```php
use Psl\Default\DefaultInterface;

enum TransportMethod implements DefaultInterface
{
    case Air;
    case Water;
    case Land;

    public static function default() : static
    {
        return self::Land;
    }
}

// Obtaining a default instance of TransportMethod
$transport_method = TransportMethod::default();
```

## API

### Interfaces

---

* [`interface DefaultInterface`](DefaultInterface.php)

    Defines a contract for creating default instances of implementing classes.

    Classes that implement this interface are expected to provide a static default() method, 
    which returns a new instance of the class, initialized with a default set of values or settings.

    This pattern is beneficial in scenarios where an object needs to be provided with a set of sensible defaults
    but also allows for customization through further configuration of the returned default instance.

    Implementing the `DefaultInterface` signals that a class supports this standardized mechanism for obtaining default instances, ensuring consistency across the PSL.

    ```php
    use Psl\Default;

    final class Example implements Default\DefaultInterface
    {
        public static function default(): static
        {
            // Return an instance with default configuration.
            return new self();
        }
    }

    $example = Example::default();
    ```
