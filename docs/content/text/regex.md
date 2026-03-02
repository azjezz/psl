# Regex

The `Regex` component provides type-safe regular expression functions. PHP's `preg_*` functions return `false` on invalid patterns and use `null` for unmatched groups, making it easy to silently propagate errors. PSL's `Regex` functions throw `InvalidPatternException` for bad patterns and support typed capture groups so you know exactly what shape your matches will have.

## Usage

### Checking for Matches

@example('text/regex-matching.php')

### Extracting Matches

`first_match()` returns the first match as an array, or `null` if nothing matched:

@example('text/regex-first-match.php')

### Typed Capture Groups

Pass a `Type` shape to get a well-typed result that validates the match structure at runtime:

@example('text/regex-typed-capture.php')

The `capture_groups()` helper builds a shape type from a list of expected group keys:

@example('text/regex-capture-groups.php')

### Finding All Matches

`every_match()` returns all matches as a list, or `null` if none matched:

@example('text/regex-every-match.php')

### Search and Replace

@example('text/regex-replace.php')

### Splitting

@example('text/regex-split.php')

## Error Handling

All functions throw instead of returning error sentinels. An invalid pattern like `/unclosed(group` throws `Regex\Exception\InvalidPatternException` immediately rather than returning `false`.

See `src/Psl/Regex/` for the full API.
