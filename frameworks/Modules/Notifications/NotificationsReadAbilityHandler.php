<?php

declare(strict_types=1);

namespace WPEssential\Modules\Notifications;

if (!defined('ABSPATH')) { exit; }

use InvalidArgumentException; use RuntimeException; use WPEssential\Contracts\AbilityHandlerInterface; use WPEssential\Platform\Auth\ExecutionContext;

final readonly class NotificationsReadAbilityHandler implements AbilityHandlerInterface
{
    public const GET='get'; public const CATALOG='catalog';
    public function __construct(private NotificationsReadService $service,private string $action){if(!in_array($this->action,[self::GET,self::CATALOG],true)){throw new InvalidArgumentException('Unsupported Notifications read ability action.');}}
    public function handle(array $input,ExecutionContext $context):mixed{return match($this->action){self::GET=>$this->get($input),self::CATALOG=>$this->catalog($input),default=>throw new RuntimeException('Unsupported Notifications read ability action.')};}
    private function get(array $input):?array{$id=$input['id']??null;if(!is_string($id)||trim($id)===''){throw new InvalidArgumentException('Notifications get requires a non-empty definition id.');}return $this->service->get(trim($id));}
    private function catalog(array $input):array{if($input!==[]){throw new InvalidArgumentException('Notifications catalog does not accept input.');}return $this->service->catalog();}
}
