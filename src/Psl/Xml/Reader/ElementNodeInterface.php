<?php

declare(strict_types=1);

namespace Psl\Xml\Reader;

/**
 * I think it is sufficient to focus on XML elements instead of attributes, comments, ... to make matching decisions.
 */
interface ElementNodeInterface
{
    /**
     * Specifies how many levels deep the element is located.
     */
    public function level(): int;

    /**
     * The full element name {namespace:name}
     */
    public function name(): string;

    /**
     * The local element name without any namespace
     */
    public function localName(): string;

    /**
     * The XML namespace URI
     */
    public function namespace(): string;

    /**
     * The short XML namespace alis
     */
    public function namespaceAlias(): string;

    /**
     * List all XML attributes as key value pairs.
     *
     * @psalm-return array<string, string>
     */
    public function attributes(): array;

    /**
     * Shortcut to find out if an attribute is set on an XML element.
     */
    public function hasAttribute(string $attribute): bool;
}
