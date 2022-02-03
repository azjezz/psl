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
* refactored `Psl\IO` handles API.
* introduced a new `Psl\File` component.
* refactor `Psl\Shell\execute` to use `Psl\IO` component.
* introduced a `Psl\IO\pipe(): (Psl\IO\CloseReadHandleInterface, Psl\IO\CloseWriteHandleInterface)` function to create a pair of handles, where writes to the WriteHandle can be read from the ReadHandle.
* **BC** - `$encoding` argument for `Psl\Str` functions now accepts `Psl\Str\Encoding` instead of `?string`.
* introduced a new `Psl\Runtime` component.
* introduced a new `Psl\Network` component.
* introduced a new `Psl\TCP` component.
* introduced a new `Psl\Unix` component.
* introduced a new `Psl\Channel` component.
* introduced a new `IO\write()` function.
* introduced a new `IO\write_line()` function.
* introduced a new `IO\write_error()` function.
* introduced a new `IO\write_error_line()` functions.
* introduced a new `Psl\Html\Encoding` enum.
* **BC** - `$encoding` argument for `Psl\Html` functions now accepts `Psl\Html\Encoding` instead of `?string`.
* **BC** - `Psl\Shell\escape_command` function has been removed, no replacement is available.
* introduced a new `Psl\Math\acos` function.
* introduced a new `Psl\Math\asin` function.
* introduced a new `Psl\Math\atan` function.
* introduced a new `Psl\Math\atan2` function.
* **BC** - The type of the $numbers argument of `Psl\Math\mean` has changed to `list<int|float>` instead of `iterable<int|float>`.
* **BC** - The type of the $numbers argument of `Psl\Math\median` has changed to `list<int|float>` instead of `iterable<int|float>`.
* introduced a new `Psl\Promise` component.
* **BC** - `Psl\Result\ResultInterface` now implements `Psl\Promise\PromiseInterface`
* **BC** - `Psl\Type\resource('curl')->toString()` now uses PHP built-in resource kind notation ( i.e: `resource (curl)` ) instead of generic notation ( i.e: `resource<curl>` )
* **BC** - `Psl\Str`, `Psl\Str\Byte`, and `Psl\Str\Grapheme` functions now throw `Psl\Str\Exception\OutOfBoundsException` instead of `Psl\Exception\InvaraintViolationsException` when `$offset` is out-of-bounds.
* **BC** - `Psl\Collection\IndexAccessInterface::at()` now throw `Psl\Collection\Exception\OutOfBoundsException` instead of `Psl\Exception\InvariantViolationException` if `$k` is out-of-bounds.
* **BC** - `Psl\Collection\AccessibleCollectionInterface::slice` signature has changed from `slice(int $start, int $length): static` to `slice(int $start, ?int $length = null): static`
* **BC** - All psl functions previously accepting `callable`, now accept only `Closure`.
* **BC** - `Psl\DataStructure\QueueInterface::dequeue`, and `Psl\DataStructure\StackInterface::pop` now throw `Psl\DataStructure\Exception\UnderflowException` instead of `Psl\Exception\InvariantViolationException` when the data structure is empty.
* **BC** - `Psl\Filesystem\write_file($file, $content)` function has been removed, use `Psl\File\write($file, $content);` instead. 
  > To preserve the same behavior as the old function, use `Psl\File\write($file, $content, Filesystem\is_file($file) ? File\WriteMode::TRUNCATE : File\WriteMode::OPEN_OR_CREATE)`.
* **BC** - `Psl\Filesystem\read_file($file, $offset, $length)` function has been removed, use `Psl\File\read($file, $offset, $length)` instead.
* **BC** - `Psl\Filesystem\append_file($file, $contents)` function has been removed, use `Psl\File\write($file, $contents, File\WriteMode::APPEND)` instead.
* **BC** - `Psl\Filesystem` functions no longer throw `Psl\Exception\InvariantViolationException`.

  New exceptions:
  - `Psl\Filesystem\Exception\NotReadableException` thrown when attempting to read from a non-readable node
  - `Psl\Filesystem\Exception\NotFileException` thrown when attempting a file operation on a non-file node.
  - `Psl\Filesystem\Exception\NotDirectoryException` thrown when attempting a directory operation on a non-directory node.
  - `Psl\Filesystem\Exception\NotSymbolicLinkException` thrown when attempting a symbolic link operation on a non-symbolic link node.
  - `Psl\Filesystem\Exception\NotFoundException` thrown when attempting an operation on a non-existing node.
* introduced `Psl\Hash\Algorithm` enum.
* introduced `Psl\Hash\Hmac\Algorithm` enum.
* **BC** - `Psl\Hash\hash`, and `Psl\Hash\Context::forAlgorithm` now take `Psl\Hash\Algorithm` as an algorithm, rather than a string.
* **BC** - `Psl\Hash\Hmac\hash`, and `Psl\Hash\Context::hmac` now take `Psl\Hash\Hmac\Algorithm` as an algorithm, rather than a string.
* **BC** - A new method `chunk(positive-int $size): CollectionInterface` has been added to `Psl\Collection\CollectionInterface`.
* introduced a new `Psl\OS` component.
* introduced `Psl\Password\Algorithm` enum
* **BC** - all constants of `Psl\Password` component has been removed.
* **BC** - function `Psl\Password\algorithms()` have been removed.
* **BC** - `Psl\Result\ResultInterface::getException()` method has been renamed to `Psl\Result\ResultInterface::getThrowable()`
* **BC** - `Psl\Result\wrap` function now catches all `Throwable`s instead of only `Exception`s
* introduced a new `Psl\Result\reflect` function
* **BC** - `Psl\Shell\escape_argument` function has been removed, `Shell\execute` arguments are now always escaped.
* **BC** - `$escape_arguments` argument of `Shell\execute` function has been removed.
* introduced a new `Psl\Shell\ErrorOutputBehavior` enum
* added a new `$error_output_behavior` argument to `Shell\execute` function, which can be used to return the command error output content, as well as the standard output content.
