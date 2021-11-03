# Changelog

## 2.0.0

* **BC** - removed `Psl\Arr` component.
* **BC** - removed `Psl\Type\is_array`, `Psl\Type\is_arraykey`, `Psl\Type\is_bool`, `Psl\Type\is_callable`, `Psl\Type\is_float`, `Psl\Type\is_instanceof`, `Psl\Type\is_int`, `Psl\Type\is_iterable`, `Psl\Type\is_null`, `Psl\Type\is_numeric`, `Psl\Type\is_object`, `Psl\Type\is_resource`, `Psl\Type\is_scalar`, and `Psl\Type\is_string` functions ( use `TypeInterface::matches($value)` instead ).
* **BC** - removed `Psl\Iter\chain`, `Psl\Iter\chunk`, `Psl\Iter\chunk_with_keys`, `Psl\Iter\diff_by_key`, `Psl\Iter\drop`, `Psl\Iter\drop_while`, `Psl\Iter\enumerate`, `Psl\Iter\filter`, `Psl\Iter\filter_keys`, `Psl\Iter\filter_nulls`, `Psl\Iter\filter_with_key`, `Psl\Iter\flat_map`, `Psl\Iter\flatten`, `Psl\Iter\flip`, `Psl\Iter\from_entries`, `Psl\Iter\from_keys`, `Psl\Iter\keys`, `Psl\Iter\map`, `Psl\Iter\map_keys`, `Psl\Iter\map_with_key`, `Psl\Iter\merge`, `Psl\Iter\product`, `Psl\Iter\pull`, `Psl\Iter\pull_with_key`, `Psl\Iter\range`, `Psl\Iter\reductions`, `Psl\Iter\reindex`, `Psl\Iter\repeat`, `Psl\Iter\reproduce`, `Psl\Iter\reverse`, `Psl\Iter\slice`, `Psl\Iter\take`, `Psl\Iter\take_while`, `Psl\Iter\to_array`, `Psl\Iter\to_array_with_keys`, `Psl\Iter\values`, and `Psl\Iter\zip` functions.
* **BC** - signature of `Psl\Iter\reduce_keys` function changed from `reduce_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(?Ts, Tk): Ts) $function, Ts|null $initial = null): Ts|null` to `reduce_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(Ts, Tk): Ts) $function, Ts $initial): Ts`.
* **BC** - signature of `Psl\Iter\reduce_with_keys` function changed from `reduce_with_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(?Ts, Tk, Tv): Ts) $function, Ts|null $initial = null): Ts|null` to `reduce_with_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(Ts, Tk, Tv): Ts) $function, Ts $initial): Ts`.
* **BC** - removed bundled psalm plugin `Psl\Integration\Psalm\Plugin`, use `php-standard-library/psalm-plugin` package instead.
* dropped support for PHP 8.0
* **BC** - signature of `Psl\Type\object` function changed from `object<T of object>(classname<T> $classname): TypeInterface<T>` to `object(): TypeInterface<object>` ( to preserve the old behavior, use `Psl\Type\instance_of` )
* introduced `Psl\Type\instance_of` function, with the signature of `instance_of<T of object>(classname<T> $classname): TypeInterface<T>`.
* introduced a new `Psl\Async` component.
* introduced a new `Psl\IO\Stream` component.
* refactored `Psl\IO` handles API.
* introduced a new `Psl\File` component.
* refactor `Psl\Filesystem\write_file`, `Psl\Filesystem\append_file`, and `Psl\Filesystem\read_file` to use `Psl\File` component.
