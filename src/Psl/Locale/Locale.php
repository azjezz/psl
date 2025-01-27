<?php

declare(strict_types=1);

namespace Psl\Locale;

use Locale as NativeLocale;
use Psl\Str;

/**
 * Represents a locale identifier.
 */
enum Locale: string
{
    case Afrikaans = 'af';
    case AfrikaansNamibia = 'af_NA';
    case AfrikaansSouthAfrica = 'af_ZA';
    case Aghem = 'agq';
    case AghemCameroon = 'agq_CM';
    case Akan = 'ak';
    case AkanGhana = 'ak_GH';
    case Amharic = 'am';
    case AmharicEthiopia = 'am_ET';
    case Arabic = 'ar';
    case ArabicUnitedArabEmirates = 'ar_AE';
    case ArabicBahrain = 'ar_BH';
    case ArabicDjibouti = 'ar_DJ';
    case ArabicAlgeria = 'ar_DZ';
    case ArabicEgypt = 'ar_EG';
    case ArabicWesternSahara = 'ar_EH';
    case ArabicEritrea = 'ar_ER';
    case ArabicIsrael = 'ar_IL';
    case ArabicIraq = 'ar_IQ';
    case ArabicJordan = 'ar_JO';
    case ArabicComoros = 'ar_KM';
    case ArabicKuwait = 'ar_KW';
    case ArabicLebanon = 'ar_LB';
    case ArabicLibya = 'ar_LY';
    case ArabicMorocco = 'ar_MA';
    case ArabicMauritania = 'ar_MR';
    case ArabicOman = 'ar_OM';
    case ArabicPalestine = 'ar_PS';
    case ArabicQatar = 'ar_QA';
    case ArabicSaudiArabia = 'ar_SA';
    case ArabicSudan = 'ar_SD';
    case ArabicSomalia = 'ar_SO';
    case ArabicSouthSudan = 'ar_SS';
    case ArabicSyria = 'ar_SY';
    case ArabicChad = 'ar_TD';
    case ArabicTunisia = 'ar_TN';
    case ArabicYemen = 'ar_YE';
    case Assamese = 'as';
    case AssameseIndia = 'as_IN';
    case Asu = 'asa';
    case AsuTanzania = 'asa_TZ';
    case Asturian = 'ast';
    case AsturianSpain = 'ast_ES';
    case Azerbaijani = 'az';
    case AzerbaijaniCyrillic = 'az_Cyrl';
    case AzerbaijaniLatin = 'az_Latn';
    case Basaa = 'bas';
    case BasaaCameroon = 'bas_CM';
    case Belarusian = 'be';
    case BelarusianBelarus = 'be_BY';
    case Bemba = 'bem';
    case BembaZambia = 'bem_ZM';
    case Bena = 'bez';
    case BenaTanzania = 'bez_TZ';
    case Bulgarian = 'bg';
    case BulgarianBulgaria = 'bg_BG';
    case Haryanvi = 'bgc';
    case HaryanviIndia = 'bgc_IN';
    case Bhojpuri = 'bho';
    case BhojpuriIndia = 'bho_IN';
    case Anii = 'blo';
    case AniiBenin = 'blo_BJ';
    case Bambara = 'bm';
    case BambaraMali = 'bm_ML';
    case Bangla = 'bn';
    case BanglaBangladesh = 'bn_BD';
    case BanglaIndia = 'bn_IN';
    case Tibetan = 'bo';
    case TibetanChina = 'bo_CN';
    case TibetanIndia = 'bo_IN';
    case Breton = 'br';
    case BretonFrance = 'br_FR';
    case Bodo = 'brx';
    case BodoIndia = 'brx_IN';
    case Bosnian = 'bs';
    case BosnianCyrillic = 'bs_Cyrl';
    case BosnianLatin = 'bs_Latn';
    case Catalan = 'ca';
    case CatalanAndorra = 'ca_AD';
    case CatalanSpain = 'ca_ES';
    case CatalanFrance = 'ca_FR';
    case CatalanItaly = 'ca_IT';
    case Chakma = 'ccp';
    case ChakmaBangladesh = 'ccp_BD';
    case ChakmaIndia = 'ccp_IN';
    case Chechen = 'ce';
    case ChechenRussia = 'ce_RU';
    case Cebuano = 'ceb';
    case CebuanoPhilippines = 'ceb_PH';
    case Chiga = 'cgg';
    case ChigaUganda = 'cgg_UG';
    case Cherokee = 'chr';
    case CherokeeUnitedStates = 'chr_US';
    case CentralKurdish = 'ckb';
    case CentralKurdishIraq = 'ckb_IQ';
    case CentralKurdishIran = 'ckb_IR';
    case Czech = 'cs';
    case CzechCzechia = 'cs_CZ';
    case SwampyCree = 'csw';
    case SwampyCreeCanada = 'csw_CA';
    case Chuvash = 'cv';
    case ChuvashRussia = 'cv_RU';
    case Welsh = 'cy';
    case WelshUnitedKingdom = 'cy_GB';
    case Danish = 'da';
    case DanishDenmark = 'da_DK';
    case DanishGreenland = 'da_GL';
    case Taita = 'dav';
    case TaitaKenya = 'dav_KE';
    case German = 'de';
    case GermanAustria = 'de_AT';
    case GermanBelgium = 'de_BE';
    case GermanSwitzerland = 'de_CH';
    case GermanGermany = 'de_DE';
    case GermanItaly = 'de_IT';
    case GermanLiechtenstein = 'de_LI';
    case GermanLuxembourg = 'de_LU';
    case Zarma = 'dje';
    case ZarmaNiger = 'dje_NE';
    case Dogri = 'doi';
    case DogriIndia = 'doi_IN';
    case LowerSorbian = 'dsb';
    case LowerSorbianGermany = 'dsb_DE';
    case Duala = 'dua';
    case DualaCameroon = 'dua_CM';
    case JolaFonyi = 'dyo';
    case JolaFonyiSenegal = 'dyo_SN';
    case Dzongkha = 'dz';
    case DzongkhaBhutan = 'dz_BT';
    case Embu = 'ebu';
    case EmbuKenya = 'ebu_KE';
    case Ewe = 'ee';
    case EweGhana = 'ee_GH';
    case EweTogo = 'ee_TG';
    case Greek = 'el';
    case GreekCyprus = 'el_CY';
    case GreekGreece = 'el_GR';
    case English = 'en';
    case EnglishUnitedArabEmirates = 'en_AE';
    case EnglishAntiguaBarbuda = 'en_AG';
    case EnglishAnguilla = 'en_AI';
    case EnglishAmericanSamoa = 'en_AS';
    case EnglishAustria = 'en_AT';
    case EnglishAustralia = 'en_AU';
    case EnglishBarbados = 'en_BB';
    case EnglishBelgium = 'en_BE';
    case EnglishBurundi = 'en_BI';
    case EnglishBermuda = 'en_BM';
    case EnglishBahamas = 'en_BS';
    case EnglishBotswana = 'en_BW';
    case EnglishBelize = 'en_BZ';
    case EnglishCanada = 'en_CA';
    case EnglishCocosKeelingIslands = 'en_CC';
    case EnglishSwitzerland = 'en_CH';
    case EnglishCookIslands = 'en_CK';
    case EnglishCameroon = 'en_CM';
    case EnglishChristmasIsland = 'en_CX';
    case EnglishCyprus = 'en_CY';
    case EnglishGermany = 'en_DE';
    case EnglishDiegoGarcia = 'en_DG';
    case EnglishDenmark = 'en_DK';
    case EnglishDominica = 'en_DM';
    case EnglishEritrea = 'en_ER';
    case EnglishFinland = 'en_FI';
    case EnglishFiji = 'en_FJ';
    case EnglishFalklandIslands = 'en_FK';
    case EnglishMicronesia = 'en_FM';
    case EnglishUnitedKingdom = 'en_GB';
    case EnglishGrenada = 'en_GD';
    case EnglishGuernsey = 'en_GG';
    case EnglishGhana = 'en_GH';
    case EnglishGibraltar = 'en_GI';
    case EnglishGambia = 'en_GM';
    case EnglishGuam = 'en_GU';
    case EnglishGuyana = 'en_GY';
    case EnglishHongKongSARChina = 'en_HK';
    case EnglishIndonesia = 'en_ID';
    case EnglishIreland = 'en_IE';
    case EnglishIsrael = 'en_IL';
    case EnglishIsleofMan = 'en_IM';
    case EnglishIndia = 'en_IN';
    case EnglishBritishIndianOceanTerritory = 'en_IO';
    case EnglishJersey = 'en_JE';
    case EnglishJamaica = 'en_JM';
    case EnglishKenya = 'en_KE';
    case EnglishKiribati = 'en_KI';
    case EnglishStKittsNevis = 'en_KN';
    case EnglishCaymanIslands = 'en_KY';
    case EnglishStLucia = 'en_LC';
    case EnglishLiberia = 'en_LR';
    case EnglishLesotho = 'en_LS';
    case EnglishMadagascar = 'en_MG';
    case EnglishMarshallIslands = 'en_MH';
    case EnglishMacaoSARChina = 'en_MO';
    case EnglishNorthernMarianaIslands = 'en_MP';
    case EnglishMontserrat = 'en_MS';
    case EnglishMalta = 'en_MT';
    case EnglishMauritius = 'en_MU';
    case EnglishMaldives = 'en_MV';
    case EnglishMalawi = 'en_MW';
    case EnglishMalaysia = 'en_MY';
    case EnglishNamibia = 'en_NA';
    case EnglishNorfolkIsland = 'en_NF';
    case EnglishNigeria = 'en_NG';
    case EnglishNetherlands = 'en_NL';
    case EnglishNauru = 'en_NR';
    case EnglishNiue = 'en_NU';
    case EnglishNewZealand = 'en_NZ';
    case EnglishPapuaNewGuinea = 'en_PG';
    case EnglishPhilippines = 'en_PH';
    case EnglishPakistan = 'en_PK';
    case EnglishPitcairnIslands = 'en_PN';
    case EnglishPuertoRico = 'en_PR';
    case EnglishPalau = 'en_PW';
    case EnglishRwanda = 'en_RW';
    case EnglishSolomonIslands = 'en_SB';
    case EnglishSeychelles = 'en_SC';
    case EnglishSudan = 'en_SD';
    case EnglishSweden = 'en_SE';
    case EnglishSingapore = 'en_SG';
    case EnglishStHelena = 'en_SH';
    case EnglishSlovenia = 'en_SI';
    case EnglishSierraLeone = 'en_SL';
    case EnglishSouthSudan = 'en_SS';
    case EnglishSintMaarten = 'en_SX';
    case EnglishEswatini = 'en_SZ';
    case EnglishTurksCaicosIslands = 'en_TC';
    case EnglishTokelau = 'en_TK';
    case EnglishTonga = 'en_TO';
    case EnglishTrinidadTobago = 'en_TT';
    case EnglishTuvalu = 'en_TV';
    case EnglishTanzania = 'en_TZ';
    case EnglishUganda = 'en_UG';
    case EnglishUSOutlyingIslands = 'en_UM';
    case EnglishUnitedStates = 'en_US';
    case EnglishStVincentGrenadines = 'en_VC';
    case EnglishBritishVirginIslands = 'en_VG';
    case EnglishUSVirginIslands = 'en_VI';
    case EnglishVanuatu = 'en_VU';
    case EnglishSamoa = 'en_WS';
    case EnglishSouthAfrica = 'en_ZA';
    case EnglishZambia = 'en_ZM';
    case EnglishZimbabwe = 'en_ZW';
    case Esperanto = 'eo';
    case Spanish = 'es';
    case SpanishArgentina = 'es_AR';
    case SpanishBolivia = 'es_BO';
    case SpanishBrazil = 'es_BR';
    case SpanishBelize = 'es_BZ';
    case SpanishChile = 'es_CL';
    case SpanishColombia = 'es_CO';
    case SpanishCostaRica = 'es_CR';
    case SpanishCuba = 'es_CU';
    case SpanishDominicanRepublic = 'es_DO';
    case SpanishCeutaMelilla = 'es_EA';
    case SpanishEcuador = 'es_EC';
    case SpanishSpain = 'es_ES';
    case SpanishEquatorialGuinea = 'es_GQ';
    case SpanishGuatemala = 'es_GT';
    case SpanishHonduras = 'es_HN';
    case SpanishCanaryIslands = 'es_IC';
    case SpanishMexico = 'es_MX';
    case SpanishNicaragua = 'es_NI';
    case SpanishPanama = 'es_PA';
    case SpanishPeru = 'es_PE';
    case SpanishPhilippines = 'es_PH';
    case SpanishPuertoRico = 'es_PR';
    case SpanishParaguay = 'es_PY';
    case SpanishElSalvador = 'es_SV';
    case SpanishUnitedStates = 'es_US';
    case SpanishUruguay = 'es_UY';
    case SpanishVenezuela = 'es_VE';
    case Estonian = 'et';
    case EstonianEstonia = 'et_EE';
    case Basque = 'eu';
    case BasqueSpain = 'eu_ES';
    case Ewondo = 'ewo';
    case EwondoCameroon = 'ewo_CM';
    case Persian = 'fa';
    case PersianAfghanistan = 'fa_AF';
    case PersianIran = 'fa_IR';
    case Fula = 'ff';
    case FulaAdlam = 'ff_Adlm';
    case FulaLatin = 'ff_Latn';
    case FulaLatinNigeria = 'ff_Latn_NG';
    case FulaLatinSenegal = 'ff_Latn_SG';
    case Finnish = 'fi';
    case FinnishFinland = 'fi_FI';
    case Filipino = 'fil';
    case FilipinoPhilippines = 'fil_PH';
    case Faroese = 'fo';
    case FaroeseDenmark = 'fo_DK';
    case FaroeseFaroeIslands = 'fo_FO';
    case French = 'fr';
    case FrenchBelgium = 'fr_BE';
    case FrenchBurkinaFaso = 'fr_BF';
    case FrenchBurundi = 'fr_BI';
    case FrenchBenin = 'fr_BJ';
    case FrenchStBarthelemy = 'fr_BL';
    case FrenchCanada = 'fr_CA';
    case FrenchCongoKinshasa = 'fr_CD';
    case FrenchCentralAfricanRepublic = 'fr_CF';
    case FrenchCongoBrazzaville = 'fr_CG';
    case FrenchSwitzerland = 'fr_CH';
    case FrenchCotedIvoire = 'fr_CI';
    case FrenchCameroon = 'fr_CM';
    case FrenchDjibouti = 'fr_DJ';
    case FrenchAlgeria = 'fr_DZ';
    case FrenchFrance = 'fr_FR';
    case FrenchGabon = 'fr_GA';
    case FrenchFrenchGuiana = 'fr_GF';
    case FrenchGuinea = 'fr_GN';
    case FrenchGuadeloupe = 'fr_GP';
    case FrenchEquatorialGuinea = 'fr_GQ';
    case FrenchHaiti = 'fr_HT';
    case FrenchComoros = 'fr_KM';
    case FrenchLuxembourg = 'fr_LU';
    case FrenchMorocco = 'fr_MA';
    case FrenchMonaco = 'fr_MC';
    case FrenchStMartin = 'fr_MF';
    case FrenchMadagascar = 'fr_MG';
    case FrenchMali = 'fr_ML';
    case FrenchMartinique = 'fr_MQ';
    case FrenchMauritania = 'fr_MR';
    case FrenchMauritius = 'fr_MU';
    case FrenchNewCaledonia = 'fr_NC';
    case FrenchNiger = 'fr_NE';
    case FrenchFrenchPolynesia = 'fr_PF';
    case FrenchStPierreMiquelon = 'fr_PM';
    case FrenchReunion = 'fr_RE';
    case FrenchRwanda = 'fr_RW';
    case FrenchSeychelles = 'fr_SC';
    case FrenchSenegal = 'fr_SN';
    case FrenchSyria = 'fr_SY';
    case FrenchChad = 'fr_TD';
    case FrenchTogo = 'fr_TG';
    case FrenchTunisia = 'fr_TN';
    case FrenchVanuatu = 'fr_VU';
    case FrenchWallisFutuna = 'fr_WF';
    case FrenchMayotte = 'fr_YT';
    case Friulian = 'fur';
    case FriulianItaly = 'fur_IT';
    case WesternFrisian = 'fy';
    case WesternFrisianNetherlands = 'fy_NL';
    case Irish = 'ga';
    case IrishUnitedKingdom = 'ga_GB';
    case IrishIreland = 'ga_IE';
    case ScottishGaelic = 'gd';
    case ScottishGaelicUnitedKingdom = 'gd_GB';
    case Galician = 'gl';
    case GalicianSpain = 'gl_ES';
    case SwissGerman = 'gsw';
    case SwissGermanSwitzerland = 'gsw_CH';
    case SwissGermanFrance = 'gsw_FR';
    case SwissGermanLiechtenstein = 'gsw_LI';
    case Gujarati = 'gu';
    case GujaratiIndia = 'gu_IN';
    case Gusii = 'guz';
    case GusiiKenya = 'guz_KE';
    case Manx = 'gv';
    case ManxIsleofMan = 'gv_IM';
    case Hausa = 'ha';
    case HausaGhana = 'ha_GH';
    case HausaNiger = 'ha_NE';
    case HausaNigeria = 'ha_NG';
    case Hawaiian = 'haw';
    case HawaiianUnitedStates = 'haw_US';
    case Hebrew = 'he';
    case HebrewIsrael = 'he_IL';
    case Hindi = 'hi';
    case HindiIndia = 'hi_IN';
    case HindiLatin = 'hi_Latn';
    case Croatian = 'hr';
    case CroatianBosniaHerzegovina = 'hr_BA';
    case CroatianCroatia = 'hr_HR';
    case UpperSorbian = 'hsb';
    case UpperSorbianGermany = 'hsb_DE';
    case Hungarian = 'hu';
    case HungarianHungary = 'hu_HU';
    case Armenian = 'hy';
    case ArmenianArmenia = 'hy_AM';
    case Interlingua = 'ia';
    case Indonesian = 'id';
    case IndonesianIndonesia = 'id_ID';
    case Interlingue = 'ie';
    case InterlingueEstonia = 'ie_EE';
    case Igbo = 'ig';
    case IgboNigeria = 'ig_NG';
    case SichuanYi = 'ii';
    case SichuanYiChina = 'ii_CN';
    case Icelandic = 'is';
    case IcelandicIceland = 'is_IS';
    case Italian = 'it';
    case ItalianSwitzerland = 'it_CH';
    case ItalianItaly = 'it_IT';
    case ItalianSanMarino = 'it_SM';
    case ItalianVaticanCity = 'it_VA';
    case Japanese = 'ja';
    case JapaneseJapan = 'ja_JP';
    case Ngomba = 'jgo';
    case NgombaCameroon = 'jgo_CM';
    case Machame = 'jmc';
    case MachameTanzania = 'jmc_TZ';
    case Javanese = 'jv';
    case JavaneseIndonesia = 'jv_ID';
    case Georgian = 'ka';
    case GeorgianGeorgia = 'ka_GE';
    case Kabyle = 'kab';
    case KabyleAlgeria = 'kab_DZ';
    case Kamba = 'kam';
    case KambaKenya = 'kam_KE';
    case Makonde = 'kde';
    case MakondeTanzania = 'kde_TZ';
    case Kabuverdianu = 'kea';
    case KabuverdianuCapeVerde = 'kea_CV';
    case Kaingang = 'kgp';
    case KaingangBrazil = 'kgp_BR';
    case KoyraChiini = 'khq';
    case KoyraChiiniMali = 'khq_ML';
    case Kikuyu = 'ki';
    case KikuyuKenya = 'ki_KE';
    case Kazakh = 'kk';
    case KazakhKazakhstan = 'kk_KZ';
    case Kako = 'kkj';
    case KakoCameroon = 'kkj_CM';
    case Kalaallisut = 'kl';
    case KalaallisutGreenland = 'kl_GL';
    case Kalenjin = 'kln';
    case KalenjinKenya = 'kln_KE';
    case Khmer = 'km';
    case KhmerCambodia = 'km_KH';
    case Kannada = 'kn';
    case KannadaIndia = 'kn_IN';
    case Korean = 'ko';
    case KoreanChina = 'ko_CN';
    case KoreanNorthKorea = 'ko_KP';
    case KoreanSouthKorea = 'ko_KR';
    case Konkani = 'kok';
    case KonkaniIndia = 'kok_IN';
    case Kashmiri = 'ks';
    case KashmiriArabic = 'ks_Arab';
    case KashmiriDevanagari = 'ks_Deva';
    case Shambala = 'ksb';
    case ShambalaTanzania = 'ksb_TZ';
    case Bafia = 'ksf';
    case BafiaCameroon = 'ksf_CM';
    case Colognian = 'ksh';
    case ColognianGermany = 'ksh_DE';
    case Kurdish = 'ku';
    case KurdishTurkiye = 'ku_TR';
    case Cornish = 'kw';
    case CornishUnitedKingdom = 'kw_GB';
    case Kuvi = 'kxv';
    case KuviDevanagari = 'kxv_Deva';
    case KuviLatin = 'kxv_Latn';
    case KuviOdia = 'kxv_Orya';
    case KuviTelugu = 'kxv_Telu';
    case Kyrgyz = 'ky';
    case KyrgyzKyrgyzstan = 'ky_KG';
    case Langi = 'lag';
    case LangiTanzania = 'lag_TZ';
    case Luxembourgish = 'lb';
    case LuxembourgishLuxembourg = 'lb_LU';
    case Ganda = 'lg';
    case GandaUganda = 'lg_UG';
    case Ligurian = 'lij';
    case LigurianItaly = 'lij_IT';
    case Lakota = 'lkt';
    case LakotaUnitedStates = 'lkt_US';
    case Lombard = 'lmo';
    case LombardItaly = 'lmo_IT';
    case Lingala = 'ln';
    case LingalaAngola = 'ln_AO';
    case LingalaCongoKinshasa = 'ln_CD';
    case LingalaCentralAfricanRepublic = 'ln_CF';
    case LingalaCongoBrazzaville = 'ln_CG';
    case Lao = 'lo';
    case LaoLaos = 'lo_LA';
    case NorthernLuri = 'lrc';
    case NorthernLuriIraq = 'lrc_IQ';
    case NorthernLuriIran = 'lrc_IR';
    case Lithuanian = 'lt';
    case LithuanianLithuania = 'lt_LT';
    case LubaKatanga = 'lu';
    case LubaKatangaCongoKinshasa = 'lu_CD';
    case Luo = 'luo';
    case LuoKenya = 'luo_KE';
    case Luyia = 'luy';
    case LuyiaKenya = 'luy_KE';
    case Latvian = 'lv';
    case LatvianLatvia = 'lv_LV';
    case Maithili = 'mai';
    case MaithiliIndia = 'mai_IN';
    case Masai = 'mas';
    case MasaiKenya = 'mas_KE';
    case MasaiTanzania = 'mas_TZ';
    case Meru = 'mer';
    case MeruKenya = 'mer_KE';
    case Morisyen = 'mfe';
    case MorisyenMauritius = 'mfe_MU';
    case Malagasy = 'mg';
    case MalagasyMadagascar = 'mg_MG';
    case MakhuwaMeetto = 'mgh';
    case MakhuwaMeettoMozambique = 'mgh_MZ';
    case Meta = 'mgo';
    case MetaCameroon = 'mgo_CM';
    case Mori = 'mi';
    case MoriNewZealand = 'mi_NZ';
    case Macedonian = 'mk';
    case MacedonianNorthMacedonia = 'mk_MK';
    case Malayalam = 'ml';
    case MalayalamIndia = 'ml_IN';
    case Mongolian = 'mn';
    case MongolianMongolia = 'mn_MN';
    case Manipuri = 'mni';
    case ManipuriBangla = 'mni_Beng';
    case Marathi = 'mr';
    case MarathiIndia = 'mr_IN';
    case Malay = 'ms';
    case MalayBrunei = 'ms_BN';
    case MalayIndonesia = 'ms_ID';
    case MalayMalaysia = 'ms_MY';
    case MalaySingapore = 'ms_SG';
    case Maltese = 'mt';
    case MalteseMalta = 'mt_MT';
    case Mundang = 'mua';
    case MundangCameroon = 'mua_CM';
    case Burmese = 'my';
    case BurmeseMyanmarBurma = 'my_MM';
    case Mazanderani = 'mzn';
    case MazanderaniIran = 'mzn_IR';
    case Nama = 'naq';
    case NamaNamibia = 'naq_NA';
    case NorwegianBokml = 'nb';
    case NorwegianBokmlNorway = 'nb_NO';
    case NorwegianBokmlSvalbardJanMayen = 'nb_SJ';
    case NorthNdebele = 'nd';
    case NorthNdebeleZimbabwe = 'nd_ZW';
    case LowGerman = 'nds';
    case LowGermanGermany = 'nds_DE';
    case LowGermanNetherlands = 'nds_NL';
    case Nepali = 'ne';
    case NepaliIndia = 'ne_IN';
    case NepaliNepal = 'ne_NP';
    case Dutch = 'nl';
    case DutchAruba = 'nl_AW';
    case DutchBelgium = 'nl_BE';
    case DutchCaribbeanNetherlands = 'nl_BQ';
    case DutchCuracao = 'nl_CW';
    case DutchNetherlands = 'nl_NL';
    case DutchSuriname = 'nl_SR';
    case DutchSintMaarten = 'nl_SX';
    case Kwasio = 'nmg';
    case KwasioCameroon = 'nmg_CM';
    case NorwegianNynorsk = 'nn';
    case NorwegianNynorskNorway = 'nn_NO';
    case Ngiemboon = 'nnh';
    case NgiemboonCameroon = 'nnh_CM';
    case Norwegian = 'no';
    case NKo = 'nqo';
    case NKoGuinea = 'nqo_GN';
    case Nuer = 'nus';
    case NuerSouthSudan = 'nus_SS';
    case Nyankole = 'nyn';
    case NyankoleUganda = 'nyn_UG';
    case Occitan = 'oc';
    case OccitanSpain = 'oc_ES';
    case OccitanFrance = 'oc_FR';
    case Oromo = 'om';
    case OromoEthiopia = 'om_ET';
    case OromoKenya = 'om_KE';
    case Odia = 'or';
    case OdiaIndia = 'or_IN';
    case Ossetic = 'os';
    case OsseticGeorgia = 'os_GE';
    case OsseticRussia = 'os_RU';
    case Punjabi = 'pa';
    case PunjabiArabic = 'pa_Arab';
    case PunjabiGurmukhi = 'pa_Guru';
    case NigerianPidgin = 'pcm';
    case NigerianPidginNigeria = 'pcm_NG';
    case Polish = 'pl';
    case PolishPoland = 'pl_PL';
    case Prussian = 'prg';
    case PrussianPoland = 'prg_PL';
    case Pashto = 'ps';
    case PashtoAfghanistan = 'ps_AF';
    case PashtoPakistan = 'ps_PK';
    case Portuguese = 'pt';
    case PortugueseAngola = 'pt_AO';
    case PortugueseBrazil = 'pt_BR';
    case PortugueseSwitzerland = 'pt_CH';
    case PortugueseCapeVerde = 'pt_CV';
    case PortugueseEquatorialGuinea = 'pt_GQ';
    case PortugueseGuineaBissau = 'pt_GW';
    case PortugueseLuxembourg = 'pt_LU';
    case PortugueseMacaoSARChina = 'pt_MO';
    case PortugueseMozambique = 'pt_MZ';
    case PortuguesePortugal = 'pt_PT';
    case PortugueseSaoTomePrincipe = 'pt_ST';
    case PortugueseTimorLeste = 'pt_TL';
    case Quechua = 'qu';
    case QuechuaBolivia = 'qu_BO';
    case QuechuaEcuador = 'qu_EC';
    case QuechuaPeru = 'qu_PE';
    case Rajasthani = 'raj';
    case RajasthaniIndia = 'raj_IN';
    case Romansh = 'rm';
    case RomanshSwitzerland = 'rm_CH';
    case Rundi = 'rn';
    case RundiBurundi = 'rn_BI';
    case Romanian = 'ro';
    case RomanianMoldova = 'ro_MD';
    case RomanianRomania = 'ro_RO';
    case Rombo = 'rof';
    case RomboTanzania = 'rof_TZ';
    case Russian = 'ru';
    case RussianBelarus = 'ru_BY';
    case RussianKyrgyzstan = 'ru_KG';
    case RussianKazakhstan = 'ru_KZ';
    case RussianMoldova = 'ru_MD';
    case RussianRussia = 'ru_RU';
    case RussianUkraine = 'ru_UA';
    case Kinyarwanda = 'rw';
    case KinyarwandaRwanda = 'rw_RW';
    case Rwa = 'rwk';
    case RwaTanzania = 'rwk_TZ';
    case Sanskrit = 'sa';
    case SanskritIndia = 'sa_IN';
    case Yakut = 'sah';
    case YakutRussia = 'sah_RU';
    case Samburu = 'saq';
    case SamburuKenya = 'saq_KE';
    case Santali = 'sat';
    case SantaliOlChiki = 'sat_Olck';
    case Sangu = 'sbp';
    case SanguTanzania = 'sbp_TZ';
    case Sardinian = 'sc';
    case SardinianItaly = 'sc_IT';
    case Sindhi = 'sd';
    case SindhiArabic = 'sd_Arab';
    case SindhiDevanagari = 'sd_Deva';
    case NorthernSami = 'se';
    case NorthernSamiFinland = 'se_FI';
    case NorthernSamiNorway = 'se_NO';
    case NorthernSamiSweden = 'se_SE';
    case Sena = 'seh';
    case SenaMozambique = 'seh_MZ';
    case KoyraboroSenni = 'ses';
    case KoyraboroSenniMali = 'ses_ML';
    case Sango = 'sg';
    case SangoCentralAfricanRepublic = 'sg_CF';
    case Tachelhit = 'shi';
    case TachelhitLatin = 'shi_Latn';
    case TachelhitTifinagh = 'shi_Tfng';
    case Sinhala = 'si';
    case SinhalaSriLanka = 'si_LK';
    case Slovak = 'sk';
    case SlovakSlovakia = 'sk_SK';
    case Slovenian = 'sl';
    case SlovenianSlovenia = 'sl_SI';
    case InariSami = 'smn';
    case InariSamiFinland = 'smn_FI';
    case Shona = 'sn';
    case ShonaZimbabwe = 'sn_ZW';
    case Somali = 'so';
    case SomaliDjibouti = 'so_DJ';
    case SomaliEthiopia = 'so_ET';
    case SomaliKenya = 'so_KE';
    case SomaliSomalia = 'so_SO';
    case Albanian = 'sq';
    case AlbanianAlbania = 'sq_AL';
    case AlbanianNorthMacedonia = 'sq_MK';
    case AlbanianKosovo = 'sq_XK';
    case Serbian = 'sr';
    case SerbianSerbia = 'sr_RS';
    case SerbianCyrillic = 'sr_Cyrl';
    case SerbianCyrillicSerbia = 'sr_Cyrl_RS';
    case SerbianLatin = 'sr_Latn';
    case SerbianLatinSerbia = 'sr_Latn_RS';
    case Sundanese = 'su';
    case SundaneseLatin = 'su_Latn';
    case Swedish = 'sv';
    case SwedishAlandIslands = 'sv_AX';
    case SwedishFinland = 'sv_FI';
    case SwedishSweden = 'sv_SE';
    case Swahili = 'sw';
    case SwahiliCongoKinshasa = 'sw_CD';
    case SwahiliKenya = 'sw_KE';
    case SwahiliTanzania = 'sw_TZ';
    case SwahiliUganda = 'sw_UG';
    case Syriac = 'syr';
    case SyriacIraq = 'syr_IQ';
    case SyriacSyria = 'syr_SY';
    case Silesian = 'szl';
    case SilesianPoland = 'szl_PL';
    case Tamil = 'ta';
    case TamilIndia = 'ta_IN';
    case TamilSriLanka = 'ta_LK';
    case TamilMalaysia = 'ta_MY';
    case TamilSingapore = 'ta_SG';
    case Telugu = 'te';
    case TeluguIndia = 'te_IN';
    case Teso = 'teo';
    case TesoKenya = 'teo_KE';
    case TesoUganda = 'teo_UG';
    case Tajik = 'tg';
    case TajikTajikistan = 'tg_TJ';
    case Thai = 'th';
    case ThaiThailand = 'th_TH';
    case Tigrinya = 'ti';
    case TigrinyaEritrea = 'ti_ER';
    case TigrinyaEthiopia = 'ti_ET';
    case Turkmen = 'tk';
    case TurkmenTurkmenistan = 'tk_TM';
    case Tongan = 'to';
    case TonganTonga = 'to_TO';
    case TokiPona = 'tok';
    case Turkish = 'tr';
    case TurkishCyprus = 'tr_CY';
    case TurkishTurkiye = 'tr_TR';
    case Tatar = 'tt';
    case TatarRussia = 'tt_RU';
    case Tasawaq = 'twq';
    case TasawaqNiger = 'twq_NE';
    case CentralAtlasTamazight = 'tzm';
    case CentralAtlasTamazightMorocco = 'tzm_MA';
    case Uyghur = 'ug';
    case UyghurChina = 'ug_CN';
    case Ukrainian = 'uk';
    case UkrainianUkraine = 'uk_UA';
    case Urdu = 'ur';
    case UrduIndia = 'ur_IN';
    case UrduPakistan = 'ur_PK';
    case Uzbek = 'uz';
    case UzbekArabic = 'uz_Arab';
    case UzbekCyrillic = 'uz_Cyrl';
    case UzbekLatin = 'uz_Latn';
    case Vai = 'vai';
    case VaiLatin = 'vai_Latn';
    case VaiVai = 'vai_Vaii';
    case Venetian = 'vec';
    case VenetianItaly = 'vec_IT';
    case Vietnamese = 'vi';
    case VietnameseVietnam = 'vi_VN';
    case Makhuwa = 'vmw';
    case MakhuwaMozambique = 'vmw_MZ';
    case Vunjo = 'vun';
    case VunjoTanzania = 'vun_TZ';
    case Walser = 'wae';
    case WalserSwitzerland = 'wae_CH';
    case Wolof = 'wo';
    case WolofSenegal = 'wo_SN';
    case Xhosa = 'xh';
    case XhosaSouthAfrica = 'xh_ZA';
    case Kangri = 'xnr';
    case KangriIndia = 'xnr_IN';
    case Soga = 'xog';
    case SogaUganda = 'xog_UG';
    case Yangben = 'yav';
    case YangbenCameroon = 'yav_CM';
    case Yiddish = 'yi';
    case YiddishUkraine = 'yi_UA';
    case Yoruba = 'yo';
    case YorubaBenin = 'yo_BJ';
    case YorubaNigeria = 'yo_NG';
    case Nheengatu = 'yrl';
    case NheengatuBrazil = 'yrl_BR';
    case NheengatuColombia = 'yrl_CO';
    case NheengatuVenezuela = 'yrl_VE';
    case Cantonese = 'yue';
    case CantoneseSimplified = 'yue_Hans';
    case CantoneseTraditional = 'yue_Hant';
    case Zhuang = 'za';
    case ZhuangChina = 'za_CN';
    case StandardMoroccanTamazight = 'zgh';
    case StandardMoroccanTamazightMorocco = 'zgh_MA';
    case Chinese = 'zh';
    case ChineseSimplified = 'zh_Hans';
    case ChineseTraditional = 'zh_Hant';
    case Zulu = 'zu';
    case ZuluSouthAfrica = 'zu_ZA';

    /**
     * Retrieves the system's default locale from the PHP environment settings.
     *
     * This method returns the locale configured via `intl.default_locale`. Should the PHP environment lack a specific
     * locale configuration or if the configured locale is unsupported, it defaults to `self::English` representing
     * the English language.
     *
     * The choice of English as the fallback is motivated by its global comprehension, role as a base language in
     * technology and international communication, predominance in technical documentation and support resources,
     * and its historical precedence in the tech industry. These factors ensure the fallback locale is both broadly
     * accessible and consistent with international standards.
     *
     * @return self The default locale as an enum instance, sourced from PHP settings or `self::English` as the fallback.
     *
     * @see https://www.php.net/manual/en/locale.getdefault.php
     *
     * @psalm-mutation-free
     */
    public static function default(): self
    {
        $full_locale = NativeLocale::getDefault();
        if (!$full_locale) {
            // Fallback to English if no locale is set or supported.
            return self::English;
        }

        $language = (string) NativeLocale::getPrimaryLanguage($full_locale);
        $script = (string) NativeLocale::getScript($full_locale);
        $region = (string) NativeLocale::getRegion($full_locale);

        $locale = Str\lowercase($language);
        if ($script) {
            $locale .= '_' . Str\capitalize($script);
        }

        if ($region) {
            $locale .= '_' . Str\uppercase($region);
        }

        // Attempt to match the system-configured locale with a supported enum instance,
        // defaulting to English if a precise match is unavailable.
        return self::tryFrom($locale) ?? self::tryFrom($language) ?? self::English;
    }

    /**
     * Get a human-readable name for the locale, suitable for display.
     *
     * @param Locale|null $locale The locale for which to get the name. Defaults to the current locale if not specified.
     *
     * @return non-empty-string The human-readable name of the locale.
     *
     * @psalm-mutation-free
     */
    public function getDisplayName(null|Locale $locale = null): string
    {
        /** @var non-empty-string */
        return NativeLocale::getDisplayName($this->value, $locale?->value ?? $this->value);
    }

    /**
     * Get the language code part of the locale.
     *
     * @return non-empty-string The language code.
     *
     * @psalm-mutation-free
     */
    public function getLanguage(): string
    {
        /** @var non-empty-string */
        return NativeLocale::getPrimaryLanguage($this->value);
    }

    /**
     * Get the display name of the language for the locale.
     *
     * @param Locale|null $locale The locale for which to get the language name. Defaults to the current locale if not specified.
     *
     * @return non-empty-string The display name of the language.
     *
     * @psalm-mutation-free
     */
    public function getDisplayLanguage(null|Locale $locale = null): string
    {
        /** @var non-empty-string */
        return NativeLocale::getDisplayLanguage($this->value, $locale?->value ?? $this->value);
    }

    /**
     * Checks if the locale has a script specified.
     *
     * @return bool True if the locale has a script, false otherwise.
     *
     * @psalm-mutation-free
     */
    public function hasScript(): bool
    {
        return $this->getScript() !== null;
    }

    /**
     * Get the script of the locale.
     *
     * @return non-empty-string|null The script of the locale, or null if not applicable.
     *
     * @psalm-mutation-free
     * @psalm-suppress RiskyTruthyFalsyComparison
     */
    public function getScript(): null|string
    {
        return NativeLocale::getScript($this->value) ?: null;
    }

    /**
     * Checks if the locale has a region specified.
     *
     * @return bool True if the locale has a region, false otherwise.
     *
     * @psalm-mutation-free
     */
    public function hasRegion(): bool
    {
        return $this->getRegion() !== null;
    }

    /**
     * Get the display name of the region for the locale.
     *
     * @param Locale|null $locale The locale for which to get the region name. Defaults to the current locale if not specified.
     *
     * @return non-empty-string|null The display name of the region, or null if not applicable.
     *
     * @psalm-mutation-free
     */
    public function getDisplayRegion(null|Locale $locale = null): null|string
    {
        return NativeLocale::getDisplayRegion($this->value, $locale?->value ?? $this->value) ?: null;
    }

    /**
     * Get the alpha-2 country code part of the locale, if present.
     *
     * @return non-empty-string|null The alpha-2 country code, or null if not present.
     *
     * @psalm-mutation-free
     * @psalm-suppress RiskyTruthyFalsyComparison
     */
    public function getRegion(): null|string
    {
        return NativeLocale::getRegion($this->value) ?: null;
    }
}
