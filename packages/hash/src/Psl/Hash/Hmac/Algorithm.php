<?php

declare(strict_types=1);

namespace Psl\Hash\Hmac;

enum Algorithm: string
{
    case Md2 = 'md2';
    case Md4 = 'md4';
    case Md5 = 'md5';
    case Sha1 = 'sha1';
    case Sha224 = 'sha224';
    case Sha256 = 'sha256';
    case Sha384 = 'sha384';
    case Sha512224 = 'sha512/224';
    case Sha512256 = 'sha512/256';
    case Sha512 = 'sha512';
    case Sha3224 = 'sha3-224';
    case Sha3256 = 'sha3-256';
    case Sha3384 = 'sha3-384';
    case Sha3512 = 'sha3-512';
    case Ripemd128 = 'ripemd128';
    case Ripemd160 = 'ripemd160';
    case Ripemd256 = 'ripemd256';
    case Ripemd320 = 'ripemd320';
    case Whirlpool = 'whirlpool';
    case Tiger1283 = 'tiger128,3';
    case Tiger1603 = 'tiger160,3';
    case Tiger1923 = 'tiger192,3';
    case Tiger1284 = 'tiger128,4';
    case Tiger1604 = 'tiger160,4';
    case Tiger1924 = 'tiger192,4';
    case Snefru = 'snefru';
    case Snefru256 = 'snefru256';
    case Gost = 'gost';
    case GostCrypto = 'gost-crypto';
    case Haval1283 = 'haval128,3';
    case Haval1603 = 'haval160,3';
    case Haval1923 = 'haval192,3';
    case Haval2243 = 'haval224,3';
    case Haval2563 = 'haval256,3';
    case Haval1284 = 'haval128,4';
    case Haval1604 = 'haval160,4';
    case Haval1924 = 'haval192,4';
    case Haval2244 = 'haval224,4';
    case Haval2564 = 'haval256,4';
    case Haval1285 = 'haval128,5';
    case Haval1605 = 'haval160,5';
    case Haval1925 = 'haval192,5';
    case Haval2245 = 'haval224,5';
    case Haval2565 = 'haval256,5';
}
