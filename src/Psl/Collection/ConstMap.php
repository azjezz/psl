<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * Interface ConstMap.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @template-extends ConstCollection<Pair<Tk, Tv>>
 * @template-extends ConstMapAccess<Tk, Tv>
 * @template-extends \IteratorAggregate<Tk, Tv>
 */
interface ConstMap extends ConstCollection, ConstMapAccess, \IteratorAggregate
{
}
