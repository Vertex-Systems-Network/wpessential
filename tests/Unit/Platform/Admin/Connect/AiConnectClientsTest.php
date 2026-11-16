<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Connect;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Connect\AiConnect;

/** Claude and ChatGPT connectors: secret connector links; Claude Desktop through mcp-remote. */
final class AiConnectClientsTest extends TestCase
{
    public function testClaudeAndChatGptConnectWithALink(): void
    {
        self::assertArrayHasKey('chatgpt', AiConnect::CLIENTS);
        self::assertArrayHasKey('claude-ai', AiConnect::CLIENTS);
        self::assertSame(['claude-ai', 'chatgpt'], AiConnect::LINK_CLIENTS);
    }

    public function testLinkCarriesUserSessionAndPassword(): void
    {
        $link = AiConnect::link(7, 'a1b2-uuid', 'abcd efgh ijkl');
        self::assertStringContainsString('/wpessential/v1/mcp', $link);
        $query = (string) parse_url($link, PHP_URL_QUERY);
        parse_str($query, $params);
        self::assertSame('7.a1b2-uuid.abcdefghijkl', $params[AiConnect::LINK_PARAM]);
    }

    public function testClaudeDesktopUsesMcpRemoteNotAnHttpEntry(): void
    {
        if (!function_exists('wp_json_encode')) {
            self::markTestSkipped('wp_json_encode is not available.');
        }
        $config = json_decode(AiConnect::snippet('claude-desktop', 'admin', 'pass word'), true);
        $server = $config['mcpServers']['wpessential'];
        self::assertSame('npx', $server['command']);
        self::assertContains('mcp-remote', $server['args']);
        self::assertStringStartsWith('Basic ', $server['env']['WPE_AUTH']);
    }
}
