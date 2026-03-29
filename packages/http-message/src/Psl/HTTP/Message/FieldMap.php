<?php

declare(strict_types=1);

namespace Psl\HTTP\Message;

use Countable;
use IteratorAggregate;
use Psl\Default\DefaultInterface;
use Traversable;

use function array_flip;
use function count;
use function strtolower;

/**
 * Ordered, case-insensitive collection of HTTP header fields.
 *
 * Represents the header section of an HTTP message as an ordered sequence of
 * [name, value] pairs. This structure preserves both the original field name
 * casing and the insertion order, which can be significant for proxies and
 * debugging. All lookups and comparisons are performed case-insensitively
 * per RFC 9110 Section 5.1.
 *
 * Multiple values for the same field name are fully supported. This is
 * required by the HTTP specification for fields such as Set-Cookie, Via,
 * and other fields where order matters or where combining values with a
 * comma is not appropriate. Use {@see getAll()} to retrieve all values for
 * a given field name, or {@see get()} to retrieve only the first value.
 *
 * The public API is fully immutable: all mutation methods ({@see with()},
 * {@see withAdded()}, {@see without()}) return new instances. Internally,
 * the class builds a lazy case-insensitive index on first lookup for O(1)
 * access by name, which is why the class is not declared readonly despite
 * its immutable public contract.
 *
 * Iteration via {@see getIterator()} yields fields in insertion order, and
 * {@see count()} returns the total number of field entries (fields with the
 * same name are counted separately).
 *
 * @implements IteratorAggregate<int<0, max>, list{non-empty-string, string}>
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-5 Fields
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-5.1 Field Names
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-5.2 Field Lines and Combined Field Value
 *
 * @api
 */
final class FieldMap implements Countable, IteratorAggregate, DefaultInterface
{
    /**
     * Cached singleton for the empty field map.
     *
     * Lazily initialized by {@see default()} to avoid allocating a new empty
     * instance on every call. Since the class is immutable, sharing a single
     * empty instance is safe.
     */
    private static null|self $empty = null;

    /**
     * Lazy-built case-insensitive index mapping lowercased names to field positions.
     *
     * Maps each lowercased field name to a list of integer positions in the
     * {@see $fields} array where that name appears. Built on first lookup via
     * {@see buildIndex()} and cached for subsequent lookups. Invalidated
     * implicitly by returning new instances from mutation methods.
     *
     * @var null|array<string, list<int<0, max>>>
     */
    private null|array $index = null;

    /**
     * Construct a field map from an ordered array of [name, value] pairs.
     *
     * Each pair is a two-element array where the first element is the
     * field name (non-empty string) and the second is the field value
     * (string). The pairs are stored in the order provided.
     *
     * @param list<list{non-empty-string, string}> $fields Ordered pairs of [name, value]. Each name must be a non-empty string. Values may be empty strings.
     */
    public function __construct(
        private readonly array $fields = [],
    ) {}

    /**
     * Create a field map from an array of field pairs.
     *
     * Factory method that returns the cached empty singleton when given an
     * empty array, avoiding unnecessary allocations. For non-empty arrays,
     * a new {@see FieldMap} instance is created.
     *
     * @param list<list{non-empty-string, string}> $fields Ordered pairs of [name, value]. Each name must be a non-empty string.
     *
     * @return self A field map containing the given fields, or the cached empty singleton.
     */
    public static function from(array $fields): self
    {
        if ($fields === []) {
            return self::default();
        }

        return new self($fields);
    }

    /**
     * Return the cached empty field map singleton.
     *
     * Returns a shared empty {@see FieldMap} instance. Since the class is
     * immutable, this singleton is safe to reuse across the application.
     * This implements the {@see DefaultInterface} contract.
     *
     * @return static The empty field map singleton.
     */
    public static function default(): static
    {
        /** @var static */
        return self::$empty ??= new self();
    }

    /**
     * Get the first value for a field name, or null if not present.
     *
     * Performs a case-insensitive lookup per RFC 9110 Section 5.1 and returns
     * the value of the first field entry matching the given name. When a
     * field has multiple values (e.g., multiple Set-Cookie entries), only the
     * first value is returned; use {@see getAll()} to retrieve all values.
     *
     * @param string $name The field name to look up (e.g., "Content-Type"). Matched case-insensitively.
     *
     * @return null|string The first field value, or null if no field with the given name exists.
     */
    public function get(string $name): null|string
    {
        $indices = $this->buildIndex()[strtolower($name)] ?? null;
        if ($indices === null) {
            return null;
        }

        return $this->fields[$indices[0]][1];
    }

    /**
     * Get all values for a field name.
     *
     * Performs a case-insensitive lookup per RFC 9110 Section 5.1 and returns
     * all values for the given field name in insertion order. This is essential
     * for fields that may appear multiple times, such as Set-Cookie, Via,
     * and other fields where each occurrence carries distinct semantics.
     *
     * Returns an empty array if no field with the given name exists.
     *
     * @param string $name The field name to look up (e.g., "Set-Cookie"). Matched case-insensitively.
     *
     * @return list<string> All values for the field, in insertion order. Empty if the field does not exist.
     */
    public function getAll(string $name): array
    {
        $indices = $this->buildIndex()[strtolower($name)] ?? null;
        if ($indices === null) {
            return [];
        }

        $result = [];
        foreach ($indices as $i) {
            $result[] = $this->fields[$i][1];
        }

        return $result;
    }

    /**
     * Check whether a field with the given name exists.
     *
     * Performs a case-insensitive lookup per RFC 9110 Section 5.1 to determine
     * whether at least one field entry with the given name is present.
     *
     * @param string $name The field name to check (e.g., "Content-Type"). Matched case-insensitively.
     *
     * @return bool True if at least one field with the given name exists, false otherwise.
     */
    public function has(string $name): bool
    {
        return isset($this->buildIndex()[strtolower($name)]);
    }

    /**
     * Return a new field map with the named field replaced or appended.
     *
     * All existing field entries with the same name (case-insensitive) are
     * removed, and the new field entry is inserted at the position of the
     * first match. If no existing field matches, the new entry is appended
     * at the end. This ensures that the field's relative position in the
     * header section is preserved when updating an existing field.
     *
     * Use this for fields where only a single value is meaningful (e.g.,
     * Content-Type, Content-Length, Host). For fields that support multiple
     * values, use {@see withAdded()} instead.
     *
     * @param non-empty-string $name The field name (e.g., "Content-Type"). The original casing is preserved in the stored entry.
     * @param string $value The field value to set.
     *
     * @return self A new field map with the specified field set.
     */
    public function with(string $name, string $value): self
    {
        $lower = strtolower($name);
        $indices = $this->buildIndex()[$lower] ?? null;

        if ($indices === null) {
            $fields = $this->fields;
            $fields[] = [$name, $value];

            return new self($fields);
        }

        if (count($indices) === 1 && $this->fields[$indices[0]][1] === $value) {
            return $this;
        }

        $replaceSet = array_flip($indices);
        $result = [];
        $replaced = false;
        foreach ($this->fields as $k => $pair) {
            if (isset($replaceSet[$k])) {
                if (!$replaced) {
                    $result[] = [$name, $value];
                    $replaced = true;
                }
            } else {
                $result[] = $pair;
            }
        }

        return new self($result);
    }

    /**
     * Return a new field map with an additional field entry appended.
     *
     * Appends a new [name, value] pair to the end of the field list without
     * removing any existing entries. Existing fields with the same name are
     * preserved. This is the correct method for headers that allow multiple
     * values where each occurrence has distinct semantics, such as Set-Cookie,
     * Via, Link, and other list-based fields.
     *
     * @param non-empty-string $name The field name (e.g., "Set-Cookie"). The original casing is preserved in the stored entry.
     * @param string $value The field value to append.
     *
     * @return self A new field map with the additional entry appended.
     */
    public function withAdded(string $name, string $value): self
    {
        $fields = $this->fields;
        $fields[] = [$name, $value];

        $new = new self($fields);

        if ($this->index !== null) {
            $index = $this->index;
            $index[strtolower($name)][] = count($this->fields);
            $new->index = $index;
        }

        return $new;
    }

    /**
     * Return a new field map with all fields matching the given name removed.
     *
     * Removes every field entry whose name matches the given name
     * (case-insensitive). The relative order of all remaining fields is
     * preserved. If no field matches, the returned field map is functionally
     * identical to this one (though a new instance is still created).
     *
     * @param non-empty-string $name The field name to remove (e.g., "Authorization"). Matched case-insensitively per RFC 9110 Section 5.1.
     *
     * @return self A new field map with all matching entries removed.
     */
    public function without(string $name): self
    {
        $lower = strtolower($name);
        $indices = $this->buildIndex()[$lower] ?? null;

        if ($indices === null) {
            return $this;
        }

        $removeSet = array_flip($indices);
        $result = [];
        foreach ($this->fields as $k => $pair) {
            if (isset($removeSet[$k])) {
                continue;
            }

            $result[] = $pair;
        }

        return new self($result);
    }

    /**
     * Return all fields as an ordered array of [name, value] pairs.
     *
     * Returns the raw underlying field data in insertion order. Each element
     * is a two-element array containing the field name (with original casing
     * preserved) and its value. This is useful for serialization or for
     * interoperability with other HTTP message abstractions.
     *
     * @return list<list{non-empty-string, string}> All field entries in insertion order.
     */
    public function toArray(): array
    {
        return $this->fields;
    }

    /**
     * Return the total number of field entries.
     *
     * Each field entry is counted individually, so multiple entries with the
     * same name are counted separately. For example, a field map with two
     * Set-Cookie entries and one Content-Type entry has a count of 3.
     *
     * @return int<0, max> The total number of field entries.
     */
    public function count(): int
    {
        return count($this->fields);
    }

    /**
     * Check whether the field map contains no entries.
     *
     * Returns true when the field map has zero entries. This is equivalent
     * to checking whether {@see count()} returns zero but is more expressive.
     *
     * @return bool True if the field map contains no entries, false otherwise.
     */
    public function isEmpty(): bool
    {
        return $this->fields === [];
    }

    /**
     * Iterate over all field entries in insertion order.
     *
     * Yields each field entry as a [name, value] pair, indexed by its
     * position in the field list. The iteration order matches the order in
     * which fields were added, which may be significant for HTTP/1.x
     * serialization and for fields whose ordering carries semantic meaning.
     *
     * @return Traversable<int<0, max>, list{non-empty-string, string}> Field entries in insertion order.
     */
    public function getIterator(): Traversable
    {
        yield from $this->fields;
    }

    /**
     * Build the case-insensitive lookup index on first access.
     *
     * Maps lowercased field names to lists of their positions in the $fields array,
     * enabling O(1) lookup by name while preserving insertion order.
     *
     * @return array<string, list<int<0, max>>>
     */
    private function buildIndex(): array
    {
        if ($this->index === null) {
            $index = [];
            foreach ($this->fields as $k => [$n, $_]) {
                $index[strtolower($n)][] = $k;
            }

            $this->index = $index;
        }

        return $this->index;
    }
}
