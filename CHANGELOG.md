# Changelog

## 2.0.0

### Arr

* **BC** - removed `Psl\Arr` component.
  
### Type

* **BC** - removed `Type\is_array`, `Type\is_arraykey`, `Type\is_bool`, `Type\is_callable`, `Type\is_float`, `Type\is_instanceof`, `Type\is_int`, `Type\is_iterable`, `Type\is_null`, `Type\is_numeric`, `Type\is_object`, `Type\is_resource`, `Type\is_scalar`, and `Type\is_string` functions ( use `TypeInterface::matches($value)` instead ).
  
### Iter

* **BC** - removed `Iter\chain`, `Iter\chunk`, `Iter\chunk_with_keys`, `Iter\diff_by_key`, `Iter\drop`, `Iter\drop_while`, `Iter\enumerate`, `Iter\filter`, `Iter\filter_keys`, `Iter\filter_nulls`, `Iter\filter_with_key`, `Iter\flat_map`, `Iter\flatten`, `Iter\flip`, `Iter\from_entries`, `Iter\from_keys`, `Iter\keys`, `Iter\map`, `Iter\map_keys`, `Iter\map_with_key`, `Iter\merge`, `Iter\product`, `Iter\pull`, `Iter\pull_with_key`, `Iter\range`, `Iter\reductions`, `Iter\reindex`, `Iter\repeat`, `Iter\reproduce`, `Iter\reverse`, `Iter\slice`, `Iter\take`, `Iter\take_while`, `Iter\to_array`, `Iter\to_array_with_keys`, `Iter\values`, and `Iter\zip` functions.
* **BC** - signature of `Iter\reduce_keys` function changed from `reduce_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(?Ts, Tk): Ts) $function, Ts|null $initial = null): Ts|null` to `reduce_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(Ts, Tk): Ts) $function, Ts $initial): Ts`.
* **BC** - signature of `Iter\reduce_with_keys` function changed from `reduce_with_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(?Ts, Tk, Tv): Ts) $function, Ts|null $initial = null): Ts|null` to `reduce_with_keys<Tk, Tv, Ts>(iterable<Tk, Tv> $iterable, (callable(Ts, Tk, Tv): Ts) $function, Ts $initial): Ts`.
