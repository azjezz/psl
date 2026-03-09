# Json

The `Json` component provides JSON encoding and decoding that throws typed exceptions instead of returning `false` or requiring manual `json_last_error()` checks. It defaults to sensible flags (unescaped Unicode, unescaped slashes, preserved zero fractions) so encoded output is clean and predictable.

## Usage

### Encoding

@example('text/json-encode.php')

### Decoding

@example('text/json-decode.php')

### Typed Decoding

`typed()` combines JSON decoding with PSL's `Type` system. The decoded data is validated and coerced into the expected shape in a single step -- no manual array key checks or type casting:

@example('text/json-typed.php')

## Why Use This Over json_encode/json_decode?

- **Exceptions by default**: No need to pass `JSON_THROW_ON_ERROR` or check `json_last_error()`
- **Clean defaults**: Unicode characters and slashes are not escaped, zero fractions are preserved
- **Type safety with `typed()`**: Validate the decoded structure against a `Type` in one call, eliminating manual `isset` / `is_array` checks
- **Consistent API**: Both encoding and decoding errors throw from the same `Json\Exception` hierarchy

See `src/Psl/Json/` for the full API.
