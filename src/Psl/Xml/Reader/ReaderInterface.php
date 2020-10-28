<?php

declare(strict_types=1);

namespace Psl\Xml\Reader;

use Generator;

interface ReaderInterface
{
    /**
     * This function produces raw XML based on one or more matchers.
     * Currently I've foreseen a Generator of strings.
     * However, in most cases you want a usable XML object.
     *
     * In XMLReader, there is an expand function that returns a DOMNode.
     * We could return an iterable list of psl Xml objects instead (but this is for another rfc)
     *
     * @see https://www.php.net/manual/en/xmlreader.expand.php
     *
     *
     * This method will return a generator instead of a Psl\Iter\Iterator for memory reasons.
     * An XML Reader is mostly used to iterate over very big XML documents.
     * For smaller documents, we can create an XML object as mentioned above.
     *
     *
     * An XML resource can be read multiple times, meaning the internal XML reader will re-read the complete XML.
     * This way we don't have to store all items internally or bother about any streams that are ended.
     *
     *
     * Next on error handling:
     *
     *      We will be adding an internal `stop_on_first_issue` function that detects errors on every tick.
     *      On first error, an exception will be thrown - meaning the iterator will stop further reading.
     *      XML items that are already handled, could have been processed already by the implementation.
     *
     *
     * Example usages of this method:
     * ->produce($xml, fn (NodeSequenceInterface $sequence): bool=> $sequence->current()->name() === 'user'))
     *
     * ->produce($xml, fn (NodeSequenceInterface $sequence): bool=> $sequence->matchesSequence('root', 'item', '...'))
     *
     * ->produce($xml, fn (NodeSequenceInterface $sequence): bool=> $sequence->current()->name() === 'user' && $sequence->current()->hasAttribute('x')))
     *
     *
     * This Example will produce XML for BOTH matchers and will therefor work as an "OR":
     *
     * ->produce(
     *     $xml,
     *     fn (NodeSequenceInterface $sequence): bool=> $sequence->current()->name() === 'item'),
     *     fn (NodeSequenceInterface $sequence): bool=> $sequence->current()->name() === 'user')
     * )
     *
     * @psalm-param ReaderResourceInterface $xml
     * @psalm-param non-empty-list<(callable(NodeSequenceInterface): bool)> $matchers
     *
     * @return \Generator<int, string, mixed, null>
     */
    public function produce(
        ReaderResourceInterface $xml,
        callable ...$matchers
    ): Generator;
}
