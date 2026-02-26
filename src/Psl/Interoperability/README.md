# Interoperability

The `Interoperability` component provides a set of interfaces for converting between PSL types and their PHP standard library (`stdlib`) or `intl` extension equivalents.

These interfaces establish a uniform contract for bidirectional conversion, allowing PSL types to interoperate seamlessly with native PHP types.

## Usage

```php
use Psl\DateTime\DateTime;
use Psl\DateTime\Timezone;

// Convert a PSL DateTime to a PHP DateTimeImmutable
$psl = DateTime::fromParts(Timezone::AmericaNewYork, 2024, 6, 15, 14, 30, 45);
$stdlib = $psl->toStdlib();

// Convert back from a PHP DateTimeImmutable to a PSL DateTime
$back = DateTime::fromStdlib($stdlib);

// Convert a PSL Timezone to an IntlTimeZone
$intl = Timezone::AmericaNewYork->toIntl();

// Convert back from an IntlTimeZone to a PSL Timezone
$tz = Timezone::fromIntl($intl);
```

## API

### Interfaces

---

* [`interface ToStdlib`](ToStdlib.php)

    Defines a contract for converting a PSL type to its PHP standard library equivalent.

    The template parameter `T` represents the target stdlib type.

    ```php
    use Psl\Interoperability;

    /**
     * @implements Interoperability\ToStdlib<\DateTimeImmutable>
     */
    final class MyDateTime implements Interoperability\ToStdlib
    {
        public function toStdlib(): mixed
        {
            // return a \DateTimeImmutable instance
        }
    }
    ```

---

* [`interface FromStdlib`](FromStdlib.php)

    Defines a contract for creating a PSL type from its PHP standard library equivalent.

    The template parameter `T` represents the source stdlib type.

    ```php
    use Psl\Interoperability;

    /**
     * @implements Interoperability\FromStdlib<\DateTimeImmutable>
     */
    final class MyDateTime implements Interoperability\FromStdlib
    {
        public static function fromStdlib(mixed $value): static
        {
            // create instance from a \DateTimeImmutable
        }
    }
    ```

---

* [`interface ToIntl`](ToIntl.php)

    Defines a contract for converting a PSL type to its `intl` extension equivalent.

    The template parameter `T` represents the target intl type.

---

* [`interface FromIntl`](FromIntl.php)

    Defines a contract for creating a PSL type from its `intl` extension equivalent.

    The template parameter `T` represents the source intl type.

---

## Implementations

The following PSL types implement these interfaces:

| PSL Type | ToStdlib | FromStdlib | ToIntl | FromIntl |
|---|---|---|---|---|
| `DateTime\DateTime` | `DateTimeImmutable` | `DateTimeImmutable` | `IntlCalendar` | `IntlCalendar` |
| `DateTime\Timestamp` | `DateTimeImmutable` | `DateTimeImmutable` | - | - |
| `DateTime\Duration` | `DateInterval` | - | - | - |
| `DateTime\Timezone` | `DateTimeZone` | `DateTimeZone` | `IntlTimeZone` | `IntlTimeZone` |
