<?php

declare(strict_types=1);

namespace Psl\Xml\Reader;

/**
 * The implementation will provide 'breadcrumbs' for detecting the current position inside the XML tree.
 */
interface NodeSequenceInterface
{
    public function current(): ElementNodeInterface;
    public function parent(): ?ElementNodeInterface;

    /**
     * This method can be used to validate if the complete node sequence matches.
     * E.g.
     *
     * ->matchesSequence('root', 'items', 'item', 'user')
     */
    public function matchesSequence(string ...$names): bool;

    /**
     * This method can be used to find out that a sequence has an element that matches a specific callback.
     * E.g.
     * ->hasAParentThat(static fn (ElementNodeInterface $element): bool => $element->name() === 'root')
     *
     * @param (callable(ElementNodeInterface):bool) $matcher
     */
    public function hasAParentThat(callable $matcher): bool;

    /**
     * Pop of the last element from the sequence.
     * (immutable)
     */
    public function pop(): NodeSequenceInterface;

    /**
     * Append an element to the sequence.
     * (immutable)
     */
    public function append(ElementNodeInterface $element): NodeSequenceInterface;
}
