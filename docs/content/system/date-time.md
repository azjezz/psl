# DateTime

The `DateTime` component provides immutable, timezone-aware date and time types. It replaces PHP's mutable `DateTime` class with a design where every operation returns a new instance, eliminating an entire class of mutation bugs.

The component is built around three core types: `DateTime` for calendar-aware dates, `Timestamp` for raw points in time, and `Duration` for time spans.

## Creating Dates

@example('system/datetime-creation.php')

## Timestamps

A `Timestamp` represents a precise point in time as seconds and nanoseconds since the Unix epoch, independent of any timezone:

@example('system/datetime-timestamp.php')

Use `Timestamp::monotonic()` for duration measurements -- it uses a monotonic clock unaffected by system time adjustments.

## Duration and Arithmetic

`Duration` represents a time span. All values are normalized automatically:

@example('system/datetime-duration.php')

## Comparing and Inspecting

@example('system/datetime-comparing.php')

## Formatting and Parsing

@example('system/datetime-formatting.php')

## Timezone Conversion

@example('system/datetime-timezone.php')

## Interoperability

`DateTime` and `Timestamp` convert to and from PHP's `DateTimeImmutable` and ICU's `IntlCalendar`:

@example('system/datetime-interop.php')

See `src/Psl/DateTime/` for the full API.
