<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Profiles;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Profiles\ProfileDefinition;
use WPEssential\Modules\Profiles\ProfileReadService;
use WPEssential\Modules\Profiles\ProfilesModule;
use WPEssential\Modules\Profiles\ProfilesReadAbilityHandler;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

final class ProfilesModuleTest extends TestCase
{
    public function testModuleRegistersBoundedReadOnlyAbilitiesAndBridgeExposure(): void
    {
        $definitions=new InMemoryDefinitionRepository(); $services=new ServiceRegistry(); $abilities=new AbilityRegistry(new PolicyEngine(new class implements CapabilityCheckerInterface{public function can(ExecutionContext $context,string $capability):bool{return true;}}));
        $environment=new class implements WordPressAbilityEnvironmentInterface{public function abilitiesApiAvailable():bool{return true;}public function doingAction(string $hook):bool{return $hook==='wp_abilities_api_init';}public function currentUserId():?int{return 1;}public function currentSiteId():int{return 1;}public function currentNetworkId():?int{return null;}public function currentUserCan(string $capability):bool{return true;}public function isRestRequest():bool{return false;}public function isCli():bool{return false;}public function registerCategory(string $slug,array $args):bool{return true;}public function registerAbility(string $name,array $args):bool{return true;}};
        $bridge=new WordPressAbilityBridge($abilities,$environment,new WordPressExecutionContextFactory($environment)); $services->set('platform.definitions',$definitions);$services->set('platform.abilities',$abilities);$services->set('platform.abilities.wordpress',$bridge);(new ProfilesModule())->register($services);
        self::assertInstanceOf(ProfileReadService::class,$services->get(ProfilesModule::SERVICE_READ)); foreach([ProfilesModule::ABILITY_GET,ProfilesModule::ABILITY_CATALOG] as $name){$d=$abilities->descriptor($name);self::assertNotNull($d);self::assertFalse($d->mutates);self::assertSame(ProfileDefinition::OWNER_SURFACE_ID,$d->ownerSurfaceId);self::assertSame(ProfilesModule::CAPABILITY,$d->capability);self::assertTrue($d->allows(ExecutionChannel::Internal));self::assertTrue($d->allows(ExecutionChannel::Ui));self::assertTrue($d->allows(ExecutionChannel::Rest));}self::assertCount(2,$bridge->registerAbilities());
    }
    public function testHandlerDelegatesReadOnlyGetAndCatalog():void{$service=new ProfileReadService(new InMemoryDefinitionRepository());$context=new ExecutionContext(new Principal(1),1);self::assertNull((new ProfilesReadAbilityHandler($service,ProfilesReadAbilityHandler::GET))->handle(['id'=>'missing'],$context));self::assertSame([],(new ProfilesReadAbilityHandler($service,ProfilesReadAbilityHandler::CATALOG))->handle([],$context));}
    public function testHandlerRejectsMutationShapedCatalogInput():void{$this->expectException(InvalidArgumentException::class);$service=new ProfileReadService(new InMemoryDefinitionRepository());(new ProfilesReadAbilityHandler($service,ProfilesReadAbilityHandler::CATALOG))->handle(['save'=>true],new ExecutionContext(new Principal(1),1));}
}
