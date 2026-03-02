# Interoperability

The `Interoperability` component provides a set of interfaces for converting between PSL types and their PHP standard library (`stdlib`) or `intl` extension equivalents.

These interfaces establish a uniform contract for bidirectional conversion, allowing PSL types to interoperate seamlessly with native PHP types.

## Usage

@example('other/interoperability-conversion.php')

## Conversion Interfaces

The component defines four interfaces covering all conversion directions:

- **`ToStdlib`** -- convert a PSL type to its PHP standard library equivalent (e.g. PSL `DateTime` to `DateTimeImmutable`)
- **`FromStdlib`** -- create a PSL type from its PHP standard library equivalent (e.g. `DateTimeImmutable` to PSL `DateTime`)
- **`ToIntl`** -- convert a PSL type to its `intl` extension equivalent (e.g. PSL `Timezone` to `IntlTimeZone`)
- **`FromIntl`** -- create a PSL type from its `intl` extension equivalent (e.g. `IntlTimeZone` to PSL `Timezone`)

## Implementations

The following PSL types implement these interfaces:

| PSL Type | ToStdlib | FromStdlib | ToIntl | FromIntl |
|---|---|---|---|---|
| `DateTime\DateTime` | `DateTimeImmutable` | `DateTimeImmutable` | `IntlCalendar` | `IntlCalendar` |
| `DateTime\Timestamp` | `DateTimeImmutable` | `DateTimeImmutable` | - | - |
| `DateTime\Duration` | `DateInterval` | - | - | - |
| `DateTime\Timezone` | `DateTimeZone` | `DateTimeZone` | `IntlTimeZone` | `IntlTimeZone` |

See `src/Psl/Interoperability/` for the full API.
