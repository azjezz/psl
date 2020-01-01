<?php

declare(strict_types=1);

namespace Psl\Collection;

use IteratorAggregate;

/**
 * Interface ConstMap.
 *
 * @template Tk as array-key
 * @template Tv
 *
 * @extends ConstCollection<Pair<Tk, Tv>>
 * @extends ConstMapAccess<Tk, Tv>
 * @extends IteratorAggregate<Tk, Tv>
 */
interface ConstMap extends ConstCollection, ConstMapAccess, IteratorAggregate
{
}
