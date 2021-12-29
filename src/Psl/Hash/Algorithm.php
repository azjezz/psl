<?php

declare(strict_types=1);

namespace Psl\Hash;

enum Algorithm: string
{
    case MD2 = "md2";
    case MD4 = "md4";
    case MD5 = "md5";
    case SHA1 = "sha1";
    case SHA224 = "sha224";
    case SHA256 = "sha256";
    case SHA384 = "sha384";
    case SHA512_224 = "sha512/224";
    case SHA512_256 = "sha512/256";
    case SHA512 = "sha512";
    case SHA3_224 = "sha3-224";
    case SHA3_256 = "sha3-256";
    case SHA3_384 = "sha3-384";
    case SHA3_512 = "sha3-512";
    case RIPEMD_128 = "ripemd128";
    case RIPEMD_160 = "ripemd160";
    case RIPEMD_256 = "ripemd256";
    case RIPEMD_320 = "ripemd320";
    case WHIRLPOOL = "whirlpool";
    case TIGER128_3 = "tiger128,3";
    case TIGER160_3 = "tiger160,3";
    case TIGER192_3 = "tiger192,3";
    case TIGER128_4 = "tiger128,4";
    case TIGER160_4 = "tiger160,4";
    case TIGER192_4 = "tiger192,4";
    case SNEFRU = "snefru";
    case SNEFRU_256 = "snefru256";
    case GOST = "gost";
    case GOST_CRYPTO = "gost-crypto";
    case ADLER32 = "adler32";
    case CRC32 = "crc32";
    case CRC32B = "crc32b";
    case CRC32C = "crc32c";
    case FNV132 = "fnv132";
    case FNV1A32 = "fnv1a32";
    case FNV164 = "fnv164";
    case FNV1A64 = "fnv1a64";
    case JOAAT = "joaat";
    case MURMUR3A = "murmur3a";
    case MURMUR3C = "murmur3c";
    case MURMUR3F = "murmur3f";
    case XXH32 = "xxh32";
    case XXH64 = "xxh64";
    case XXH3 = "xxh3";
    case XXH128 = "xxh128";
    case HAVAL128_3 = "haval128,3";
    case HAVAL160_3 = "haval160,3";
    case HAVAL192_3 = "haval192,3";
    case HAVAL224_3 = "haval224,3";
    case HAVAL256_3 = "haval256,3";
    case HAVAL128_4 = "haval128,4";
    case HAVAL160_4 = "haval160,4";
    case HAVAL192_4 = "haval192,4";
    case HAVAL224_4 = "haval224,4";
    case HAVAL256_4 = "haval256,4";
    case HAVAL128_5 = "haval128,5";
    case HAVAL160_5 = "haval160,5";
    case HAVAL192_5 = "haval192,5";
    case HAVAL224_5 = "haval224,5";
    case HAVAL256_5 = "haval256,5";
}
