<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

enum ScreenMode: string
{
    /**
     * Toggles cursor blinking (DEC private mode 12).
     */
    case CursorBlink = '?12';

    /**
     * Enables automatic line wrapping when the cursor reaches the right margin (DEC private mode 7).
     */
    case AutoWrap = '?7';

    /**
     * Allows the cursor to reverse-wrap to the previous line when backspacing past the left margin (DEC private mode 45).
     */
    case ReverseWrap = '?45';

    /**
     * Restricts cursor movement to the scrolling region and makes coordinates relative to its origin (DEC private mode 6).
     */
    case Origin = '?6';

    /**
     * Switches to the alternate screen buffer, providing a clean canvas for full-screen TUIs (DEC private mode 1049).
     */
    case AlternateScreen = '?1049';

    /**
     * Batches terminal output so the screen is painted in one go, eliminating flicker (DEC private mode 2026).
     */
    case SynchronizedOutput = '?2026';

    /**
     * Enables mouse button event reporting with SGR extended coordinates (DEC private modes 1000 + 1006).
     */
    case MouseTracking = '?1000;1006';

    /**
     * Enables mouse button and motion event reporting with SGR extended coordinates (DEC private modes 1003 + 1006).
     */
    case MouseMotionTracking = '?1003;1006';

    /**
     * Wraps pasted content with delimiter sequences so the application can distinguish it from typed input (DEC private mode 2004).
     */
    case BracketedPaste = '?2004';

    /**
     * Reports focus-in and focus-out events when the terminal window gains or loses focus (DEC private mode 1004).
     */
    case FocusTracking = '?1004';

    /**
     * Sends resize events as escape sequences instead of (or in addition to) SIGWINCH signals (DEC private mode 2048).
     */
    case InBandResize = '?2048';

    /**
     * Enables Unicode grapheme cluster awareness for cursor movement and cell width calculation (DEC private mode 2027).
     */
    case GraphemeClustering = '?2027';

    /**
     * Reports changes to the terminal's light/dark color scheme (DEC private mode 2031).
     */
    case ColorSchemeReporting = '?2031';
}
