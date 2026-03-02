# Default

The `Default` component introduces a uniform approach for classes within PSL to provide and utilize default instances.

It encapsulates the `DefaultInterface`, establishing a contract that enables implementing classes to offer a standardized method for instantiating themselves with default values or settings. This pattern is beneficial when an object needs sensible defaults but also allows customization through further configuration.

## Usage

@example('types/default-enum.php')

Classes and enums that implement `DefaultInterface` provide a static `default()` method returning a new instance initialized with default values:

@example('types/default-class.php')

Implementing `DefaultInterface` signals that a class supports this standardized mechanism for obtaining default instances, ensuring consistency across PSL.

See `src/Psl/Default/` for the full API.
