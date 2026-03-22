<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\SMTP\Client\Authentication;
use Psl\SMTP\Client\Transport;
use Psl\SMTP\Client\TransportConfiguration;
use Psl\SMTP\Security;

// PLAIN authentication
$plain = new Authentication\PlainAuthenticator('user', 'password');

// LOGIN authentication
$login = new Authentication\LoginAuthenticator('user', 'password');

// XOAUTH2 for Gmail and other OAuth2-enabled servers
$oauth = new Authentication\XOAuth2Authenticator('user@gmail.com', 'oauth2-access-token');

// CRAM-MD5 challenge-response (avoids sending password in plaintext)
$cramMd5 = new Authentication\CRAMMD5Authenticator('user', 'password');

// SCRAM-SHA-256 with mutual authentication (RFC 7677)
$scram = new Authentication\SCRAMSHA256Authenticator('user', 'password');

// Pass any authenticator as the second argument to Transport
$transport = new Transport(
    TransportConfiguration::default()->withHost('smtp.gmail.com')->withSecurity(Security::TLS),
    $oauth,
);
