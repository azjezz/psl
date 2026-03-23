<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\System\Internal;

use PHPUnit\Framework\TestCase;
use Psl\DNS\System\Internal\ScutilDnsParser;

use function file_get_contents;

final class ScutilDnsParserTest extends TestCase
{
    public function testRealMacosOutput(): void
    {
        $content = file_get_contents(__DIR__ . '/../../../fixture/systemconfig/scutil-dns-macos.txt');

        $config = ScutilDnsParser::parse($content);

        $globalNameservers = [];
        $scopedNameservers = [];
        foreach ($config->nameservers as $ns) {
            if ($ns->forDomains === []) {
                $globalNameservers[] = $ns;
            } else {
                $scopedNameservers[] = $ns;
            }
        }

        static::assertCount(1, $globalNameservers);
        static::assertSame('1.1.1.1', $globalNameservers[0]->host);
        static::assertSame(53, $globalNameservers[0]->port);

        static::assertCount(1, $scopedNameservers);
        static::assertSame('127.0.0.1', $scopedNameservers[0]->host);
        static::assertSame(2053, $scopedNameservers[0]->port);
        static::assertSame(['test'], $scopedNameservers[0]->forDomains);

        static::assertContains('test', $config->searchDomains);
    }

    public function testSkipsMulticastResolvers(): void
    {
        $output = <<<'OUTPUT'
        DNS configuration

        resolver #1
          nameserver[0] : 8.8.8.8
          flags    : Request A records
          reach    : 0x00000002 (Reachable)

        resolver #2
          domain   : local
          options  : mdns
          timeout  : 5
          flags    : Request A records
          reach    : 0x00000000 (Not Reachable)
          order    : 300000
        OUTPUT;

        $config = ScutilDnsParser::parse($output);

        static::assertCount(1, $config->nameservers);
        static::assertSame('8.8.8.8', $config->nameservers[0]->host);
    }

    public function testSkipsScopedResolvers(): void
    {
        $output = <<<'OUTPUT'
        DNS configuration

        resolver #1
          nameserver[0] : 8.8.8.8
          flags    : Request A records
          reach    : 0x00000002 (Reachable)

        DNS configuration (for scoped queries)

        resolver #1
          nameserver[0] : 192.168.1.1
          if_index : 6 (en0)
          flags    : Scoped, Request A records
          reach    : 0x00000002 (Reachable)
        OUTPUT;

        $config = ScutilDnsParser::parse($output);

        static::assertCount(1, $config->nameservers);
        static::assertSame('8.8.8.8', $config->nameservers[0]->host);
    }

    public function testMultipleNameserversInBlock(): void
    {
        $output = <<<'OUTPUT'
        DNS configuration

        resolver #1
          nameserver[0] : 8.8.8.8
          nameserver[1] : 8.8.4.4
          flags    : Request A records
          reach    : 0x00000002 (Reachable)
        OUTPUT;

        $config = ScutilDnsParser::parse($output);

        static::assertCount(2, $config->nameservers);
        static::assertSame('8.8.8.8', $config->nameservers[0]->host);
        static::assertSame('8.8.4.4', $config->nameservers[1]->host);
    }

    public function testSearchDomains(): void
    {
        $output = <<<'OUTPUT'
        DNS configuration

        resolver #1
          search domain[0] : example.com
          search domain[1] : corp.example.com
          nameserver[0] : 10.0.0.1
          flags    : Request A records
          reach    : 0x00000002 (Reachable)
        OUTPUT;

        $config = ScutilDnsParser::parse($output);

        static::assertSame(['example.com', 'corp.example.com'], $config->searchDomains);
    }

    public function testDomainScopedResolver(): void
    {
        $output = <<<'OUTPUT'
        DNS configuration

        resolver #1
          nameserver[0] : 8.8.8.8
          flags    : Request A records
          reach    : 0x00000002 (Reachable)

        resolver #2
          domain   : corp.internal
          nameserver[0] : 10.0.0.53
          flags    : Request A records
          reach    : 0x00000002 (Reachable)
        OUTPUT;

        $config = ScutilDnsParser::parse($output);

        static::assertCount(2, $config->nameservers);

        static::assertSame('8.8.8.8', $config->nameservers[0]->host);
        static::assertSame([], $config->nameservers[0]->forDomains);

        static::assertSame('10.0.0.53', $config->nameservers[1]->host);
        static::assertSame(['corp.internal'], $config->nameservers[1]->forDomains);
    }

    public function testCustomPort(): void
    {
        $output = <<<'OUTPUT'
        DNS configuration

        resolver #1
          domain   : test
          nameserver[0] : 127.0.0.1
          port     : 2053
          flags    : Request A records
          reach    : 0x00000002 (Reachable)
        OUTPUT;

        $config = ScutilDnsParser::parse($output);

        static::assertCount(1, $config->nameservers);
        static::assertSame('127.0.0.1', $config->nameservers[0]->host);
        static::assertSame(2053, $config->nameservers[0]->port);
    }

    public function testEmptyOutput(): void
    {
        $config = ScutilDnsParser::parse('DNS configuration');

        static::assertCount(0, $config->nameservers);
        static::assertCount(0, $config->searchDomains);
    }

    public function testDeduplicatesNameservers(): void
    {
        $output = <<<'OUTPUT'
        DNS configuration

        resolver #1
          nameserver[0] : 8.8.8.8
          flags    : Request A records

        resolver #2
          nameserver[0] : 8.8.8.8
          flags    : Request A records
        OUTPUT;

        $config = ScutilDnsParser::parse($output);

        static::assertCount(1, $config->nameservers);
    }

    public function testIpv6WithInterfaceSuffix(): void
    {
        $output = <<<'OUTPUT'
        DNS configuration

        resolver #1
          nameserver[0] : fe80::1%en0
          flags    : Request A records
          reach    : 0x00000002 (Reachable)
        OUTPUT;

        $config = ScutilDnsParser::parse($output);

        static::assertCount(1, $config->nameservers);
        static::assertSame('fe80::1', $config->nameservers[0]->host);
    }
}
