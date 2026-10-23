<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Core;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Connect\AiConnect;
use WPEssential\Platform\Admin\Core\AiSwitchAjaxHandler;

final class AiSwitchAjaxHandlerTest extends TestCase
{
    public function testWritesCannotBeAllowedWhileAiConnectIsOff(): void
    {
        $connect = new AiConnect();
        $handler = new AiSwitchAjaxHandler($connect);

        $handler->handle(['op' => 'writes', 'enabled' => true]);
        self::assertFalse($connect->allowWrites());

        $result = $handler->handle(['op' => 'power', 'enabled' => true]);
        self::assertTrue($connect->enabled());
        self::assertTrue($result['reload']);

        $handler->handle(['op' => 'writes', 'enabled' => true]);
        self::assertTrue($connect->allowWrites());

        $handler->handle(['op' => 'power', 'enabled' => false]);
        self::assertFalse($connect->allowWrites());
    }

    public function testUnknownSwitchIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new AiSwitchAjaxHandler(new AiConnect()))->handle(['op' => 'grant-admin', 'enabled' => true]);
    }
}
