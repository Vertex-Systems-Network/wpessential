<?php

declare(strict_types=1);

namespace WPEssential\Modules\Cron;

if (!defined('ABSPATH')) { exit; }

use InvalidArgumentException; use RuntimeException; use WPEssential\Contracts\AbilityHandlerInterface; use WPEssential\Platform\Auth\ExecutionContext;

final readonly class CronReadAbilityHandler implements AbilityHandlerInterface
{
    public const GET='get'; public const CATALOG='catalog';
    public function __construct(private CronReadService $service,private string $action){if(!in_array($this->action,[self::GET,self::CATALOG],true)){throw new InvalidArgumentException('Unsupported Cron read ability action.');}}
    public function handle(array $input,ExecutionContext $context):mixed{return match($this->action){self::GET=>$this->get($input),self::CATALOG=>$this->catalog($input),default=>throw new RuntimeException('Unsupported Cron read ability action.')};}
    private function get(array $input):?array{$id=$input['id']??null;if(!is_string($id)||trim($id)===''){throw new InvalidArgumentException('Cron get requires a non-empty definition id.');}return $this->service->get(trim($id));}
    private function catalog(array $input):array{if($input!==[]){throw new InvalidArgumentException('Cron catalog does not accept input.');}return $this->service->catalog();}
}
