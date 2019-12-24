<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface for mutable `Set`s to enable removal of its values.
 *
 * @psalm-template Tv as array-key
 *
 * @template-extends ConstSetAccess<Tv>
 */
interface SetAccess extends ConstSetAccess
{
    /**
     * Removes the provided value from the current `Set`.
     *
     * If the value is not in the current `Set`, the `Set` is unchanged.
     *
     * It the current `Set`, meaning changes  made to the current `Set` will be
     * reflected in the returned `Set`.
     *
     * @psalm-param Tv $m - The value to remove
     *
     * @psalm-return ConstSetAccess<Tv> - Returns itself.
     */
    public function remove($m): ConstSetAccess;
}
