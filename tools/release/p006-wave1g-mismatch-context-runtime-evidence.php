<?php

declare(strict_types=1);

const P006G_FREE_SHA = '2acf6d202315e7105b89589822a95448febdc06634c0399dfcfc57b702e14d80';
const P006G_PRO_SHA = '771bf516e9c1d0707a7d036309b14520facf5daa59742b337b0a0fdf622c0363';
const P006G_PAIR = '39a31275225f1b560a22096fcbd66aef8878bc23554c5b32c6861a6a446215ea';
const P006G_FIXTURES = ['FP-23', 'FP-25', 'FP-26', 'FP-27', 'FP-28'];
const P006G_PRO_MODULES = ['roles','admin-menu','settings','dashboard','profiles','membership','builder-widgets','forms-workflows','cron','notifications','emails','chat'];
const P006G_VARIANTS = [
    'old' => ['version'=>'0.0.9','file'=>'wpessential-fp19-0.0.9.zip','sha'=>'c67c6b67be87b2ca21f3f6bfdb97430b0580846444a2441665f414885ca224b6','pair'=>'b9ea99b645dd9abcadbf6110cbeed85ba2ea73cf79940596cd7aa5bb5204eece','state'=>'free_version_too_old','reason'=>'free_version_below_supported_minimum','remediation'=>'update_free'],
    'new' => ['version'=>'0.1.1','file'=>'wpessential-fp20-0.1.1.zip','sha'=>'819c6674cecd42b4f9ba2e7226c5913c616f31d49187568af07383a0a0bc6471','pair'=>'890f74a7f8bc560be3311fb9dab438e47e658350a227674eac4dffb3d7155e5d','state'=>'free_version_too_new','reason'=>'free_version_above_supported_maximum','remediation'=>'update_pro'],
];

function gFail(string $m): never { throw new RuntimeException($m); }
function gAssert(bool $ok, string $m): void { if (!$ok) gFail($m); }
function gEnv(string $k): string { $v=trim((string)getenv($k)); if ($v==='') gFail("Missing env: {$k}"); return $v; }
function gHash(string $p): string { gAssert(is_file($p) && filesize($p)>0,"Missing artifact: {$p}"); $h=hash_file('sha256',$p); gAssert(is_string($h),'Hash failed'); return $h; }
function gWrite(string $p,array $v): void { if (!is_dir(dirname($p))) mkdir(dirname($p),0775,true); gAssert(file_put_contents($p,json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR).PHP_EOL)!==false,"Write failed: {$p}"); }
function gRead(string $p): array { $v=json_decode((string)file_get_contents($p),true,flags:JSON_THROW_ON_ERROR); gAssert(is_array($v),"Invalid JSON: {$p}"); return $v; }
function gPair(string $free,string $pro): string { return hash('sha256',"free:{$free}\npro:{$pro}\n"); }

/** @return array<string,mixed> */
function gIdentity(): array {
    $source=gEnv('WPE_P006_SOURCE_SHA');
    gAssert(preg_match('/^[0-9a-f]{40}$/',$source)===1,'Invalid source SHA');
    $original=gHash(gEnv('WPE_P006_ORIGINAL_FREE_ZIP_PATH'));
    $pro=gHash(gEnv('WPE_P006_PRO_ZIP_PATH'));
    gAssert(hash_equals(P006G_FREE_SHA,$original),'Original Free hash drift; STOP');
    gAssert(hash_equals(P006G_PRO_SHA,$pro),'Pro hash drift; STOP');
    gAssert(hash_equals(P006G_PAIR,gPair($original,$pro)),'Original pair drift; STOP');
    $variants=[];
    foreach (P006G_VARIANTS as $id=>$spec) {
        $path=rtrim(gEnv('WPE_P006_VARIANT_DIR'),'/\\').'/'.$spec['file'];
        $free=gHash($path); $pair=gPair($free,$pro);
        gAssert(hash_equals($spec['sha'],$free),"{$id} TEST-ONLY Free hash drift; STOP");
        gAssert(hash_equals($spec['pair'],$pair),"{$id} TEST-ONLY pair drift; STOP");
        $variants[$id]=['version'=>$spec['version'],'filename'=>$spec['file'],'sha256'=>$free,'pair_id_sha256'=>$pair];
    }
    return [
        'protocol'=>'P-006','wave'=>'1G-mismatch-context-runtime','source_sha'=>$source,
        'temporary_approval_id'=>'GOV-OWNER-CONSENT-P001-TEMP-P006-005','classification'=>'NON-RELEASE / TEST-ONLY',
        'derivation_boundary'=>['utility'=>'tools/release/p006-wave1f-marketing-version-mismatch-evidence.php','only_plugin_version_header_and_WPE_VERSION'=>true,'must_reproduce_wave1f_hashes'=>true],
        'original'=>['free_sha256'=>$original,'pro_sha256'=>$pro,'pair_id_sha256'=>P006G_PAIR],
        'variants'=>$variants,'authorized_fixtures'=>P006G_FIXTURES,'excluded_fixtures'=>['FP-21','FP-22','FP-24','FP-29+'],
        'certification_boundary'=>['permanent_p001_certified'=>false,'free_pro_pair_certified'=>false,'runtime_certified'=>false,'adr_0010'=>'Proposed'],
    ];
}

/** @return array<string,mixed> */
function gInput(string $mode): array {
    $variant=gEnv('WPE_P006_VARIANT_ID'); $spec=P006G_VARIANTS[$variant]??null; gAssert(is_array($spec),'Unauthorized variant');
    $wp=gEnv('WPE_P006_EXPECTED_WP'); $php=gEnv('WPE_P006_EXPECTED_PHP'); $mysql=gEnv('WPE_P006_EXPECTED_MYSQL');
    $cell = ($wp==='6.9'&&$php==='8.2'&&$mysql==='8.4')?'minimum':(($wp==='7.1'&&$php==='8.5'&&$mysql==='8.4')?'reference':null);
    gAssert(is_string($cell),'Environment outside grant -005');
    $fixture=$context=null;
    if ($mode==='verify') {
        $fixture=gEnv('WPE_P006_FIXTURE_ID'); $context=gEnv('WPE_P006_REQUEST_CONTEXT');
        gAssert(in_array($fixture,P006G_FIXTURES,true),'Fixture outside Wave 1G');
        $allowed=match($fixture){'FP-23','FP-26'=>['frontend'],'FP-25'=>['admin'],'FP-27'=>['rest'],'FP-28'=>['cron','cli'],default=>[]};
        gAssert(in_array($context,$allowed,true),"Context {$context} invalid for {$fixture}");
    }
    $freeZip=gEnv('WPE_P006_FREE_ZIP_PATH'); $proZip=gEnv('WPE_P006_PRO_ZIP_PATH');
    $freeHash=gHash($freeZip); $proHash=gHash($proZip);
    gAssert(hash_equals($spec['sha'],$freeHash),'Derived Free hash differs from authorized hash; STOP');
    gAssert(hash_equals(P006G_PRO_SHA,$proHash),'Pro hash drift; STOP');
    gAssert(hash_equals($spec['pair'],gPair($freeHash,$proHash)),'Derived pair drift; STOP');
    return ['variant_id'=>$variant,'spec'=>$spec,'cell_id'=>$cell,'expected_wp'=>$wp,'expected_php'=>$php,'expected_mysql'=>$mysql,'source_sha'=>gEnv('WPE_P006_SOURCE_SHA'),'free_zip'=>$freeZip,'pro_zip'=>$proZip,'wp_dir'=>rtrim(gEnv('WPE_TEST_WORDPRESS_DIR'),'/\\'),'evidence_dir'=>rtrim(gEnv('WPE_P006_EVIDENCE_DIR'),'/\\'),'network_log'=>gEnv('WPE_P006_NETWORK_LOG'),'fixture_id'=>$fixture,'context'=>$context];
}

function gNetworkProbe(array $in): void {
    $dir=$in['wp_dir'].'/wp-content/mu-plugins'; if(!is_dir($dir)) mkdir($dir,0775,true);
    $php=<<<'PHP'
<?php
if (!defined('WPE_P006G_NETWORK_DENY_PROBE_ACTIVE')) define('WPE_P006G_NETWORK_DENY_PROBE_ACTIVE', true);
add_filter('pre_http_request', static function ($preempt,$args,$url) {
    $log=trim((string)getenv('WPE_P006_NETWORK_LOG')); if($log!=='') file_put_contents($log,(string)$url.PHP_EOL,FILE_APPEND|LOCK_EX);
    return new WP_Error('p006_wave1g_network_blocked','Outbound HTTP denied during P-006 Wave 1G evidence.');
}, PHP_INT_MIN, 3);
PHP;
    gAssert(file_put_contents($dir.'/p006-wave1g-network-deny.php',$php.PHP_EOL)!==false,'Network probe write failed');
}
function gReset(string $p): void { if(!is_dir(dirname($p))) mkdir(dirname($p),0775,true); gAssert(file_put_contents($p,'')!==false,'Reset failed'); }
function gAttempts(array $in): array { if(!is_file($in['network_log'])) return []; $v=file($in['network_log'],FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES); return is_array($v)?array_values(array_map('strval',$v)):[]; }
function gEnvelope(string $c): void {
    $uris=['activation'=>'/wp-admin/plugins.php','frontend'=>'/','admin'=>'/wp-admin/plugins.php','rest'=>'/wp-json/wp/v2/types','cron'=>'/wp-cron.php','cli'=>'/'];
    $_SERVER['HTTP_HOST']='p006.test'; $_SERVER['SERVER_NAME']='p006.test'; $_SERVER['SERVER_PORT']='80'; $_SERVER['SERVER_PROTOCOL']='HTTP/1.1'; $_SERVER['REQUEST_METHOD']='GET'; $_SERVER['REMOTE_ADDR']='127.0.0.1'; $_SERVER['HTTPS']='off'; $_SERVER['REQUEST_URI']=$uris[$c]??'/'; $_SERVER['SCRIPT_NAME']=in_array($c,['admin','activation'],true)?'/wp-admin/plugins.php':'/index.php'; $_SERVER['PHP_SELF']=$_SERVER['SCRIPT_NAME'];
}
function gContext(string $c): void { if($c==='admin'&&!defined('WP_ADMIN'))define('WP_ADMIN',true); if($c==='rest'&&!defined('REST_REQUEST'))define('REST_REQUEST',true); if($c==='cron'&&!defined('DOING_CRON'))define('DOING_CRON',true); if($c==='cli'&&!defined('WP_CLI'))define('WP_CLI',true); }
function gEnvironment(array $in): array { global $wp_version,$wpdb; gAssert(isset($wp_version)&&is_string($wp_version),'WP version unavailable'); gAssert(isset($wpdb)&&$wpdb instanceof wpdb,'wpdb unavailable'); $php=PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION; $mysql=(string)$wpdb->db_version(); gAssert($wp_version===$in['expected_wp'],'WP cell mismatch'); gAssert($php===$in['expected_php'],'PHP cell mismatch'); gAssert(str_starts_with($mysql,$in['expected_mysql']),'MySQL cell mismatch'); return ['wordpress'=>$wp_version,'php'=>PHP_VERSION,'mysql'=>$mysql,'sapi'=>PHP_SAPI]; }
function gIncludedPro(array $in): array { $root=str_replace('\\','/',$in['wp_dir'].'/wp-content/plugins/wpessential-pro/'); $r=[]; foreach(get_included_files() as $f){$n=str_replace('\\','/',$f);if(str_starts_with($n,$root))$r[]=substr($n,strlen($root));} sort($r); return $r; }

/** @return array<string,mixed> */
function gRuntime(array $in): array {
    gAssert(is_plugin_active('wpessential/wpessential.php'),'Free must be active'); gAssert(is_plugin_active('wpessential-pro/wpessential-pro.php'),'Pro must be active');
    gAssert(defined('WPE_VERSION')&&(string)WPE_VERSION===$in['spec']['version'],'Free version drift');
    gAssert(defined('WPE_PLATFORM_API_VERSION')&&(string)WPE_PLATFORM_API_VERSION==='0.1.0','Platform API drift');
    gAssert(defined('WPE_PLATFORM_SCHEMA_GENERATION')&&(int)WPE_PLATFORM_SCHEMA_GENERATION===1,'Platform schema drift');
    $compat=$GLOBALS['wpe_pro_compatibility_result']??null; gAssert(is_array($compat),'Compatibility result absent');
    foreach(['state','reason','remediation'] as $k) gAssert(($compat[$k]??null)===$in['spec'][$k],"Compatibility {$k} drift");
    gAssert(($compat['dimension']??null)==='free_version','Compatibility dimension drift'); gAssert(($compat['premium_boot_allowed']??null)===false,'Premium boot allowed'); gAssert(($compat['premium_migrations_allowed']??null)===false,'Premium migrations allowed');
    $included=gIncludedPro($in); $unexpected=array_values(array_filter($included,static fn(string $f):bool=>$f!=='wpessential-pro.php'&&!str_starts_with($f,'frameworks/Modules/Compatibility/'))); gAssert($unexpected===[],'Premium implementation loaded: '.implode(',',$unexpected));
    gAssert(class_exists(\WPEssential\Bootstrap\Plugin::class),'Free bootstrap absent'); $kernel=\WPEssential\Bootstrap\Plugin::kernel(); gAssert($kernel instanceof \WPEssential\Kernel\Kernel&&$kernel->isBooted(),'Free kernel not booted'); $mods=$kernel->modules(); gAssert($mods->has('custom-post-types')&&$mods->has('taxonomies'),'Free CPT/Taxonomy missing');
    $proMods=[]; foreach(P006G_PRO_MODULES as $m)if($mods->has($m))$proMods[]=$m; gAssert($proMods===[],'Premium modules registered: '.implode(',',$proMods));
    return ['free_active'=>true,'pro_active'=>true,'free_version'=>(string)WPE_VERSION,'compatibility'=>$compat,'kernel_booted'=>true,'free_custom_post_types_present'=>true,'free_taxonomies_present'=>true,'unexpected_pro_modules'=>[],'included_pro_files'=>$included,'unexpected_pro_implementation_files'=>[],'premium_boot_allowed'=>false,'premium_migrations_allowed'=>false];
}
function gNoLeak(string $text,array $in): void { foreach([$in['wp_dir'],'p006-test-password-strong','p006_admin','p006-admin@example.test','wpessential-pro/frameworks/'] as $n) gAssert($n===''||!str_contains($text,$n),"Private/internal marker leaked: {$n}"); gAssert(preg_match('/(?:access|oauth|license)[_-]?(?:token|key)\s*[:=]/i',$text)!==1,'Token/key-shaped data leaked'); }

/** @return array<string,mixed> */
function gPrepare(array $in): array {
    gNetworkProbe($in); gReset($in['network_log']); gEnvelope('activation'); require $in['wp_dir'].'/wp-load.php'; require_once ABSPATH.'wp-admin/includes/plugin.php'; gAssert(defined('WPE_P006G_NETWORK_DENY_PROBE_ACTIVE'),'Network probe absent'); gAssert(is_blog_installed(),'WP not installed'); $env=gEnvironment($in);
    foreach(['wpessential-pro/wpessential-pro.php','wpessential/wpessential.php'] as $p)if(is_plugin_active($p))deactivate_plugins($p,true,false); gReset($in['network_log']);
    $a=activate_plugin('wpessential/wpessential.php','',false,true); gAssert(!is_wp_error($a)&&is_plugin_active('wpessential/wpessential.php'),'Free activation failed'); $a=activate_plugin('wpessential-pro/wpessential-pro.php','',false,true); gAssert(!is_wp_error($a)&&is_plugin_active('wpessential-pro/wpessential-pro.php'),'Pro activation failed');
    gAssert(gAttempts($in)===[],'HTTP during activation');
    return ['status'=>'PASS','phase'=>'activation','variant_id'=>$in['variant_id'],'cell_id'=>$in['cell_id'],'actual_environment'=>$env,'artifact_identity'=>['source_sha'=>$in['source_sha'],'classification'=>'NON-RELEASE / TEST-ONLY','derived_free_sha256'=>$in['spec']['sha'],'pro_sha256'=>P006G_PRO_SHA,'derived_pair_id_sha256'=>$in['spec']['pair']],'runtime_network_attempts'=>[],'certification_boundary'=>['permanent_p001_certified'=>false,'free_pro_pair_certified'=>false,'runtime_certified'=>false,'adr_0010'=>'Proposed']];
}

/** @return array<string,mixed> */
function gVerify(array $in): array {
    $activation=gRead($in['evidence_dir'].'/activation.json'); gAssert(($activation['status']??null)==='PASS','Activation evidence not PASS'); $c=$in['context']; $alreadyLoaded=defined('ABSPATH');
    if (!$alreadyLoaded) { gNetworkProbe($in); gReset($in['network_log']); gEnvelope($c); gContext($c); ob_start(); require $in['wp_dir'].'/wp-load.php'; $bootstrap=(string)ob_get_clean(); }
    else { gAssert($c==='cli' && defined('WP_CLI') && WP_CLI===true,'Preloaded WordPress is allowed only for actual WP-CLI evidence'); $bootstrap=''; }
    require_once ABSPATH.'wp-admin/includes/plugin.php'; gAssert(defined('WPE_P006G_NETWORK_DENY_PROBE_ACTIVE'),'Network probe absent'); $env=gEnvironment($in); $runtime=gRuntime($in); gNoLeak($bootstrap,$in); $f=$in['fixture_id'];
    if($f==='FP-23') $obs=['free_kernel_booted'=>true,'free_custom_post_types_owner_available'=>true,'free_taxonomies_owner_available'=>true,'premium_modules_registered'=>[]];
    elseif($f==='FP-25'){gAssert(is_admin(),'Not admin');ob_start();do_action('admin_notices');$html=(string)ob_get_clean();$expected='WPEssential Pro is inactive because local Free/Pro compatibility failed ('.$in['spec']['reason'].'). Recovery: '.$in['spec']['remediation'].'.';$text=trim(wp_strip_all_tags(html_entity_decode($html,ENT_QUOTES|ENT_HTML5,'UTF-8')));gAssert(str_contains($text,$expected),'Exact remediation notice missing');gAssert(!str_contains(strtolower($html),'<script'),'Script markup in notice');gNoLeak($html,$in);$obs=['expected_notice'=>$expected,'notice_text'=>$text,'notice_escaped'=>true,'global_admin_hijack'=>false];}
    elseif($f==='FP-26'){gAssert(!is_admin(),'Frontend became admin');ob_start();do_action('wp_head');do_action('wp_footer');$out=$bootstrap.(string)ob_get_clean();gNoLeak($out,$in);$obs=['frontend_boot_nonfatal'=>true,'captured_output_bytes'=>strlen($out),'private_internal_leak_detected'=>false];}
    elseif($f==='FP-27'){gAssert(defined('REST_REQUEST')&&REST_REQUEST===true,'REST marker absent');$server=rest_get_server();gAssert($server instanceof WP_REST_Server,'REST server absent');$routes=$server->get_routes();gAssert(is_array($routes)&&count($routes)>0,'Core REST routes absent');$after=gRuntime($in);$obs=['rest_boot_nonfatal'=>true,'registered_route_count'=>count($routes),'premium_modules_registered'=>$after['unexpected_pro_modules'],'premium_implementation_files_loaded'=>$after['unexpected_pro_implementation_files'],'free_custom_post_types_owner_available'=>true,'free_taxonomies_owner_available'=>true];}
    elseif($f==='FP-28'){gAssert(($c==='cron'&&defined('DOING_CRON')&&DOING_CRON===true)||($c==='cli'&&defined('WP_CLI')&&WP_CLI===true),'Background context marker absent');$obs=['background_context'=>$c,'php_sapi'=>PHP_SAPI,'actual_wp_cli'=>($c==='cli'&&defined('WP_CLI')&&WP_CLI===true&&$alreadyLoaded),'bootstrap_nonfatal'=>true,'premium_modules_registered'=>[],'premium_migrations_allowed'=>false,'premium_implementation_files_loaded'=>[]];}
    else gFail('Unsupported fixture');
    $attempts=gAttempts($in); gAssert($attempts===[],'Unexpected runtime HTTP: '.implode(',',$attempts));
    return ['status'=>'PASS','fixture_id'=>$f,'variant_id'=>$in['variant_id'],'cell_id'=>$in['cell_id'],'request_context'=>$c,'actual_environment'=>$env,'runtime'=>$runtime,'observation'=>$obs,'runtime_network_attempts'=>[],'certification_boundary'=>['permanent_p001_certified'=>false,'free_pro_pair_certified'=>false,'runtime_certified'=>false,'adr_0010'=>'Proposed','fp21_22_24_executed'=>false,'fp29_plus_executed'=>false]];
}

/** @return array<string,mixed> */
function gAggregate(array $in): array {
    gAssert((gRead($in['evidence_dir'].'/activation.json')['status']??null)==='PASS','Activation not PASS');
    $map=['FP-23'=>['fp23-frontend.json'],'FP-25'=>['fp25-admin.json'],'FP-26'=>['fp26-frontend.json'],'FP-27'=>['fp27-rest.json'],'FP-28'=>['fp28-cron.json','fp28-cli.json']]; $results=[];
    foreach($map as $f=>$files){$contexts=[];foreach($files as $file){$r=gRead($in['evidence_dir'].'/'.$file);gAssert(($r['status']??null)==='PASS',"{$file} not PASS");gAssert(($r['fixture_id']??null)===$f,'Fixture identity drift');gAssert(($r['runtime']['compatibility']['state']??null)===$in['spec']['state'],'Compatibility state drift');gAssert(($r['runtime_network_attempts']??null)===[],'Runtime HTTP present');if($f==='FP-28'&&($r['request_context']??null)==='cli')gAssert(($r['observation']['actual_wp_cli']??false)===true,'FP-28 CLI evidence did not execute through actual WP-CLI');$contexts[]=$r['request_context'];}$results[$f]=['status'=>'PASS','contexts'=>$contexts];}
    return ['status'=>'PASS','variant_id'=>$in['variant_id'],'cell_id'=>$in['cell_id'],'expected_test_only_free_version'=>$in['spec']['version'],'expected_compatibility_state'=>$in['spec']['state'],'formal_fixture_results'=>$results,'formal_fixture_count'=>5,'scope'=>['executed_fixtures'=>P006G_FIXTURES,'excluded_fixtures'=>['FP-21','FP-22','FP-24','FP-29+'],'matrix_cell_is_not_fixture_count'=>true,'product_release_artifact'=>false],'certification_boundary'=>['permanent_p001_certified'=>false,'free_pro_pair_certified'=>false,'runtime_certified'=>false,'adr_0010'=>'Proposed']];
}

$mode=trim((string)getenv('WPE_P006_EXECUTION_MODE')); if($mode==='')$mode=$argv[1]??'';
try {
    if($mode==='identity'){ $r=gIdentity(); gWrite(rtrim(gEnv('WPE_P006_VARIANT_DIR'),'/\\').'/p006-wave1g-candidate-identity.json',$r); fwrite(STDOUT,json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR).PHP_EOL); exit(0); }
    gAssert(in_array($mode,['prepare','verify','aggregate'],true),'Usage: <identity|prepare|verify|aggregate>'); $in=gInput($mode); $r=$mode==='prepare'?gPrepare($in):($mode==='verify'?gVerify($in):gAggregate($in));
    $file=$mode==='prepare'?'activation.json':($mode==='aggregate'?'summary.json':strtolower(str_replace('FP-','fp',(string)$in['fixture_id'])).'-'.$in['context'].'.json'); gWrite($in['evidence_dir'].'/'.$file,$r); fwrite(STDOUT,json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR).PHP_EOL);
} catch(Throwable $e){ fwrite(STDERR,'[P-006 Wave 1G] '.$e->getMessage().PHP_EOL); exit(1); }
