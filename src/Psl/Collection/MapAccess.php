<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface for setting and removing `Map` keys (and associated values).
 *
 * This interface provides no new methods as all current access for `Map`s are
 * defined in its parent interfaces. But you could theoretically use this
 * interface for parameter and return type annotations.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @template-extends ConstMapAccess<Tk, Tv>
 * @template-extends SetAccess<Tk>
 * @template-extends IndexAccess<Tk, Tv>
 */
interface MapAccess extends ConstMapAccess, SetAccess, IndexAccess
{
}
