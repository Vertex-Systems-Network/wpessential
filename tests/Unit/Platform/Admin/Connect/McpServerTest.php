<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Connect;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\Connect\AiConnect;
use WPEssential\Platform\Admin\Connect\LicenseService;
use WPEssential\Platform\Admin\Connect\Mcp\SettingsAbilities;
use WPEssential\Platform\Admin\Connect\McpServer;
use WPEssential\Platform\Admin\Core\ModuleToggleStore;
use WPEssential\Platform\Admin\Elements\ElementRegistry;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;

final class McpServerTest extends TestCase
{
    private ModuleToggleStore $toggles;
    private AiConnect $connect;

    private function server(bool $admin = true): McpServer
    {
        $this->toggles = new ModuleToggleStore();
        $this->connect = new AiConnect();
        $this->connect->set('enabled', true);
        $abilities = new AbilityRegistry(new PolicyEngine(new class ($admin) implements CapabilityCheckerInterface {
            public function __construct(private readonly bool $admin)
            {
            }

            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->admin && $context->principal->userId !== null;
            }
        }));
        (new SettingsAbilities($this->toggles, new ElementRegistry(), new LicenseService()))->register($abilities);

        return new McpServer($abilities, $this->connect, null, static fn (): int => 1);
    }

    /** @return list<string> */
    private static function toolNames(McpServer $server): array
    {
        return array_column($server->tools(), 'name');
    }

    public function testInitializeAnnouncesTheToolsCapability(): void
    {
        $response = $this->server()->dispatch(1, 'initialize', []);

        self::assertSame(McpServer::PROTOCOL, $response['result']['protocolVersion']);
        self::assertArrayHasKey('tools', $response['result']['capabilities']);
    }

    public function testOnlyReadToolsAreListedUntilWritesAreAllowed(): void
    {
        $server = $this->server();
        $names  = self::toolNames($server);

        self::assertContains('site_info', $names);
        self::assertContains('wpessential_framework_modules_list', $names);
        self::assertNotContains('wpessential_framework_modules_toggle', $names);
        self::assertNotContains('wpessential_framework_settings_update', $names);

        $this->connect->set('allow_writes', true);
        self::assertContains('wpessential_framework_modules_toggle', self::toolNames($server));
    }

    public function testHiddenWriteToolCannotBeCalled(): void
    {
        $response = $this->server()->dispatch(2, 'tools/call', ['name' => 'wpessential_framework_modules_toggle', 'arguments' => ['module' => 'relations', 'enabled' => false]]);

        self::assertSame(-32602, $response['error']['code']);
        self::assertTrue($this->toggles->isEnabled('relations'));
    }

    public function testAllowedWriteChangesTheModuleAndReportsTheChange(): void
    {
        $server = $this->server();
        $this->connect->set('allow_writes', true);
        $response = $server->dispatch(3, 'tools/call', ['name' => 'wpessential_framework_modules_toggle', 'arguments' => ['module' => 'relations', 'enabled' => false]]);

        self::assertFalse($response['result']['isError']);
        self::assertSame(['module' => 'relations', 'from' => true, 'to' => false], $response['result']['structuredContent']['changed']);
        self::assertFalse($this->toggles->isEnabled('relations'));
    }

    public function testPremiumModuleCannotBeSwitchedOnWithoutPremium(): void
    {
        Shell::configure(false);
        $server = $this->server();
        $this->connect->set('allow_writes', true);
        $response = $server->dispatch(4, 'tools/call', ['name' => 'wpessential_framework_modules_toggle', 'arguments' => ['module' => 'backup', 'enabled' => true]]);

        self::assertTrue($response['result']['isError']);
    }

    public function testCapabilityPolicyStillApplies(): void
    {
        $response = $this->server(false)->dispatch(5, 'tools/call', ['name' => 'wpessential_framework_modules_list', 'arguments' => []]);

        self::assertTrue($response['result']['isError']);
    }

    public function testTurningAiConnectOffAlsoTurnsWritesOff(): void
    {
        $this->server();
        $this->connect->set('allow_writes', true);
        $this->connect->set('enabled', false);

        self::assertFalse($this->connect->allowWrites());
    }

    public function testUnknownMethod(): void
    {
        self::assertSame(-32601, $this->server()->dispatch(6, 'resources/list', [])['error']['code']);
    }
}
