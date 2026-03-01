# Ansi

The `Ansi` component provides pure functions for constructing ANSI escape sequences — text styling, colors, cursor movement, screen manipulation, hyperlinks, and terminal mode control.

All functions return immutable sequence objects that implement `CommandInterface`. No I/O is performed — call `toString()` to get the raw escape string for writing to a terminal.

## Usage

```php
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Cursor;
use Psl\Ansi\Screen;
use Psl\Ansi\Style;
use Psl\IO;

// Style and color text
IO\write(Ansi\apply('Hello, world!', Style\bold(), Ansi\foreground(Color\green())));

// Cursor movement
IO\write(Cursor\move_to(1, 1)->toString());
IO\write(Cursor\hide()->toString());

// Screen control
IO\write(Screen\erase(Screen\EraseMode::Full)->toString());
IO\write(Screen\title('My App')->toString());

// Hyperlinks (OSC 8)
IO\write(Ansi\link('Click here', 'https://example.com', Style\underline()));

// Strip ANSI sequences from text
$plain = Ansi\strip("\e[1mBold\e[0m"); // "Bold"
```

## Design

The component is organized around three sequence types that implement `CommandInterface`:

- **`ControlSequenceIntroducer`** (CSI) — sequences starting with `\e[`, used for cursor movement, text styling (SGR), screen erasing, scrolling, and DEC private mode toggling.
- **`OperatingSystemCommand`** (OSC) — sequences starting with `\e]`, used for setting window titles, hyperlinks, clipboard access, and desktop notifications.
- **`ControlCharacter`** — single-byte control characters (e.g. BEL `\x07`) that don't fit the CSI or OSC format.

All are `final readonly` classes with a `toString()` method that produces the raw escape string. Functions in the component return one of these types (or a plain `string` for composed output like `apply()` and `link()`).

## Text Styling

The `apply()` function wraps text with SGR (Select Graphic Rendition) sequences and an automatic reset. It accepts any combination of styles and colors:

```php
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;

// Combine multiple styles
Ansi\apply('important', Style\bold(), Style\underline(), Ansi\foreground(Color\red()));

// Background colors
Ansi\apply(' PASS ', Style\bold(), Ansi\foreground(Color\white()), Ansi\background(Color\green()));
```

### Styles

Ten style functions are available in the `Style` namespace: `bold`, `dim`, `italic`, `underline`, `double_underline`, `overline`, `blink`, `reversed`, `hidden`, and `strikethrough`. Each returns a CSI sequence for use with `apply()` or `link()`.

### Colors

Colors are created through factory functions in the `Color` namespace and passed to `foreground()` or `background()`:

- **16 named colors** — `black`, `red`, `green`, `yellow`, `blue`, `magenta`, `cyan`, `white`, and their `bright_*` variants
- **ANSI-256** — `Color\ansi256(int $code)` for the 256-color palette
- **24-bit RGB** — `Color\rgb(int $r, int $g, int $b)` for true color
- **Hex** — `Color\hex(string $hex)` for hex color strings like `'#FF6600'`

### Utilities

- `strip(string $text): string` — removes all ANSI escape sequences (both CSI and OSC) from text
- `contains(string $text): bool` — checks whether text contains any ANSI escape sequences
- `reset()` — returns the SGR reset sequence (`\e[0m`)
- `bell()` — returns the BEL control character (`\x07`), which triggers an audible or visual alert in most terminals

## Hyperlinks

The `link()` function wraps text in an OSC 8 hyperlink, with optional SGR styles applied to the visible text:

```php
use Psl\Ansi;
use Psl\Ansi\Style;

Ansi\link('GitHub', 'https://github.com', Style\bold(), Style\underline());
// Produces: {SGR bold+underline}{OSC 8 open}GitHub{OSC 8 close}{SGR reset}
```

Terminal emulators that support OSC 8 render the text as a clickable link.

## Cursor Control

Functions in the `Cursor` namespace manipulate cursor position and visibility:

```php
use Psl\Ansi\Cursor;

Cursor\move_to(1, 1);       // absolute position (1-based row, column)
Cursor\up(5);               // relative movement
Cursor\down(3);
Cursor\forward(10);
Cursor\back(2);
Cursor\save();              // save position
Cursor\restore();           // restore saved position
Cursor\hide();              // hide cursor (DEC private mode ?25)
Cursor\show();              // show cursor
Cursor\request_position();  // Device Status Report (\e[6n) — terminal responds with \e[{row};{col}R
```

`request_position()` only produces the query sequence. The caller is responsible for writing it to the terminal and reading the response from stdin.

## Screen Control

Functions in the `Screen` namespace control the display, window properties, and terminal modes.

### Erasing and Scrolling

```php
use Psl\Ansi\Screen;

Screen\erase(Screen\EraseMode::Full);               // erase entire screen
Screen\erase(Screen\EraseMode::FullWithScrollback);  // erase screen + scrollback buffer
Screen\erase(Screen\EraseMode::Above);               // erase above cursor
Screen\erase_line(Screen\LineEraseMode::Right);      // erase from cursor to end of line
Screen\scroll_up(5);                                 // scroll viewport up 5 lines
Screen\scroll_down(3);                               // scroll viewport down 3 lines
```

### Window Properties (OSC)

```php
use Psl\Ansi\Screen;

Screen\title('My App');                 // set window title (OSC 2)
Screen\icon('myicon');                  // set window icon (OSC 1)
Screen\icon_and_title('My App');        // set both (OSC 0)
Screen\notify('Build complete');        // desktop notification (OSC 9)
Screen\change_directory('/home/user');  // inform terminal of CWD (OSC 7)
Screen\clipboard('copied text');        // set system clipboard via OSC 52
```

### Progress Indicator (OSC 9;4)

Display a progress bar in the terminal's tab or taskbar. Supported by Windows Terminal, ConEmu, Kitty, and Ghostty:

```php
use Psl\Ansi\Screen;

Screen\progress(Screen\ProgressState::Normal, 50);          // 50% progress
Screen\progress(Screen\ProgressState::Indeterminate);       // animated spinner
Screen\progress(Screen\ProgressState::Error, 75);           // error state at 75%
Screen\progress(Screen\ProgressState::Warning, 90);         // warning state at 90%
Screen\progress_clear();                                    // remove progress indicator
```

### Terminal Modes

These functions toggle DEC private modes — the same mechanism used by `Cursor\hide()`/`show()` for cursor visibility.

**Alternate screen buffer** (`?1049`) — provides a clean canvas for full-screen TUIs. When disabled, the original terminal content is restored:

```php
IO\write(Screen\enable_alternate_screen()->toString());
// ... draw TUI ...
IO\write(Screen\disable_alternate_screen()->toString());
```

**Mouse tracking** (`?1000` + `?1006`) — enables mouse event reporting with SGR extended coordinates, allowing the application to receive click, scroll, and motion events:

```php
IO\write(Screen\enable_mouse_tracking()->toString());
// ... read mouse events from stdin ...
IO\write(Screen\disable_mouse_tracking()->toString());
```

**Bracketed paste** (`?2004`) — when enabled, the terminal wraps pasted content with delimiter sequences so the application can distinguish pasted text from typed input. Use `bracketed_paste_start()` and `bracketed_paste_end()` to get the marker strings for detection:

```php
IO\write(Screen\enable_bracketed_paste()->toString());

$paste_start = Screen\bracketed_paste_start(); // "\e[200~"
$paste_end = Screen\bracketed_paste_end();     // "\e[201~"
// ... detect paste boundaries when reading input ...

IO\write(Screen\disable_bracketed_paste()->toString());
```

**Focus tracking** (`?1004`) — when enabled, the terminal reports focus-in (`\e[I`) and focus-out (`\e[O`) events when the window gains or loses focus:

```php
IO\write(Screen\enable_focus_tracking()->toString());
// ... read focus events from stdin ...
IO\write(Screen\disable_focus_tracking()->toString());
```

**Kitty keyboard protocol** — pushes enhanced keyboard reporting onto the terminal's mode stack, enabling features like key release events and modifier disambiguation:

```php
IO\write(Screen\enable_kitty_keyboard()->toString());     // flags=1 (disambiguate)
IO\write(Screen\enable_kitty_keyboard(3)->toString());    // flags=3 (disambiguate + event types)
// ... read enhanced key events from stdin ...
IO\write(Screen\disable_kitty_keyboard()->toString());    // pop from mode stack
```

## Examples

### Styled Log Output

```php
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\IO;

IO\write_line(Ansi\apply('Error: something went wrong', Style\bold(), Ansi\foreground(Color\red())));
IO\write_line(Ansi\apply('Warning: check configuration', Ansi\foreground(Color\yellow())));
IO\write_line(Ansi\apply('Success!', Style\bold(), Ansi\foreground(Color\green())));
```

### TUI Setup and Teardown

```php
use Psl\Ansi;
use Psl\Ansi\Cursor;
use Psl\Ansi\Screen;
use Psl\IO;

// Enter TUI mode
IO\write(
    Screen\enable_alternate_screen()->toString()
    . Cursor\hide()->toString()
    . Screen\erase(Screen\EraseMode::Full)->toString()
    . Cursor\move_to(1, 1)->toString()
    . Screen\title('My TUI App')->toString()
);

// ... render UI ...

// Exit TUI mode
IO\write(
    Cursor\show()->toString()
    . Ansi\reset()->toString()
    . Screen\disable_alternate_screen()->toString()
    . Screen\title('')->toString()
);
```

### Rainbow Text

```php
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\IO;
use Psl\Math;
use Psl\Str;

$text = 'Hello, Rainbow!';
$out = '';
foreach (Str\chunk($text) as $i => $char) {
    $hue = ($i * 25) % 360;
    $angle = ($hue * Math\PI) / 180.0;
    $r = (int)(127.5 + 127.5 * Math\sin($angle));
    $g = (int)(127.5 + 127.5 * Math\sin($angle + 2.094));
    $b = (int)(127.5 + 127.5 * Math\sin($angle + 4.189));
    $out .= Ansi\apply($char, Style\bold(), Ansi\foreground(Color\rgb($r, $g, $b)));
}

IO\write_line($out);
```

---
