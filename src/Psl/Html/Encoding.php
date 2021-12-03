<?php

declare(strict_types=1);

namespace Psl\Html;

enum Encoding: string
{
  // ASCII compatible multi-byte 8-bit Unicode.
  case UTF_8 = 'UTF-8';

  // Western European, Latin-1.
  case ISO_8859_1 = 'ISO-8859-1';

  // Western European, Latin-9. Adds the Euro sign, French and Finnish letters missing in Latin-1 (ISO-8859-1).
  case ISO_8859_15 = 'ISO-8859-15';

  // Cyrillic charset (Latin/Cyrillic).
  case ISO_8859_5 = 'ISO-8859-5';

  // DOS-specific Cyrillic charset.
  case CP_866 = 'cp866';

  // Windows-specific Cyrillic charset.
  case CP_1251 = 'cp1251';

  // Windows specific charset for Western European.
  case CP_1252 = 'cp1252';

  // Russian.
  case KOI8_R = 'KOI8-R';

  // Traditional Chinese, mainly used in Taiwan.
  case BIG5 = 'BIG5';

  // Simplified Chinese, national standard character set.
  case GB2312 = 'GB2312';

  // Big5 with Hong Kong extensions, Traditional Chinese.
  case BIG5_HKSCS = 'BIG5-HKSCS';
  
  // Japanese
  case SHIFT_JIS = 'Shift_JIS';

  // Japanese
  case EUC_JP = 'EUC-JP';

  // Charset that was used by Mac OS.
  case MAC_ROMAN = 'MacRoman';
}