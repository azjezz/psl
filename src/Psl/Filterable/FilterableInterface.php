<?php

declare(strict_types=1);

namespace Psl\Filterable;

/**
 * Defines a contract for filterable collections or objects.
 *
 * The FilterableInterface establishes a standardized approach for filtering collections
 * or objects based on a predicate function. Implementing this interface in a class indicates
 * that the class provides a mechanism to filter its elements based on custom criteria.
 *
 * This pattern is particularly useful in scenarios where you need to extract a subset
 * of elements from a collection that satisfy certain conditions, while maintaining the
 * same type and structure as the original collection.
 *
 * Implementing classes are expected to provide a `filter()` method, which accepts a
 * predicate function and returns a new instance containing only the elements that
 * satisfy the predicate condition.
 *
 * @template T The type of elements contained in the filterable object.
 */
interface FilterableInterface
{
    /**
     * Filters the elements based on a predicate function.
     *
     * This method should be implemented in such a way that it returns a new instance
     * of the same type, containing only the elements for which the predicate function
     * returns true. The original instance should remain unchanged.
     *
     * The predicate is a closure that takes an element of type T and returns a boolean
     * indicating whether the element should be included in the filtered result.
     *
     * The method's return type is `static`, implying that it returns an instance of the
     * same class where the method is implemented. This allows for fluent usage and potential
     * chaining of further operations.
     *
     * @param (Closure(T): bool) $predicate A function that determines whether an element should be included.
     *
     * @return static<T> A new instance containing only the elements that satisfy the predicate.
     */
    public function filter(\Closure $predicate);
}
