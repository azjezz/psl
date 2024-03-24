<?php

declare(strict_types=1);

namespace Psl\Default;

/**
 * Defines a contract for creating default instances of implementing classes.
 *
 * The DefaultInterface facilitates a standardized approach to obtaining default instances
 * of objects. Implementing this interface in a class indicates that the class can provide
 * a "default" instance of itself, typically configured with default settings or values.
 *
 * This pattern is particularly useful in scenarios where an object needs to be provided
 * with a sensible set of defaults, but also allowing for the possibility of customization
 * through further configuration of the returned default instance.
 *
 * Implementing classes are expected to provide a static `default()` method, which
 * returns a new instance of the class itself, initialized to a default state.
 */
interface DefaultInterface
{
    /**
     * Creates and returns a default instance of the implementing class.
     *
     * This method should be implemented in such a way that it returns a new instance
     * of the class, configured with a set of default values or settings that are considered
     * sensible or typical for most use cases.
     *
     * The method's return type is `static`, implying that it returns an instance of the
     * same class where the method is implemented. This allows for fluent usage and potential
     * chaining of further configuration methods, if provided by the class.
     *
     * @return static A default instance of the implementing class.
     */
    public static function default(): static;
}
