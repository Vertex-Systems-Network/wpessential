<?php

declare(strict_types=1);

namespace WPEssential\Modules\BuilderWidgets;

if (!defined('ABSPATH')) { exit; }

use InvalidArgumentException; use RuntimeException; use WPEssential\Contracts\AbilityHandlerInterface; use WPEssential\Platform\Auth\ExecutionContext;

final readonly class BuilderWidgetsReadAbilityHandler implements AbilityHandlerInterface
{
    public const GET='get'; public const CATALOG='catalog';
    public function __construct(private BuilderWidgetsReadService $service,private string $action){if(!in_array($this->action,[self::GET,self::CATALOG],true)){throw new InvalidArgumentException('Unsupported Builder Widgets read ability action.');}}
    public function handle(array $input,ExecutionContext $context):mixed{return match($this->action){self::GET=>$this->get($input),self::CATALOG=>$this->catalog($input),default=>throw new RuntimeException('Unsupported Builder Widgets read ability action.')};}
    private function get(array $input):?array{$id=$input['id']??null;if(!is_string($id)||trim($id)===''){throw new InvalidArgumentException('Builder Widgets get requires a non-empty definition id.');}return $this->service->get(trim($id));}
    private function catalog(array $input):array{if($input!==[]){throw new InvalidArgumentException('Builder Widgets catalog does not accept input.');}return $this->service->catalog();}
}
