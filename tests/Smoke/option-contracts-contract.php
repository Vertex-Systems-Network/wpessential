<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

/** @return array<string,mixed> */
function ocj(string $path): array {
    if (!is_file($path)) throw new RuntimeException("Missing JSON: {$path}");
    try { $v = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR); }
    catch (JsonException $e) { throw new RuntimeException("Invalid JSON {$path}: {$e->getMessage()}", 0, $e); }
    if (!is_array($v)) throw new RuntimeException("JSON root must be object: {$path}");
    return $v;
}
/** @param mixed $v */
function oca($v, string $m): array { if (!is_array($v)) throw new RuntimeException($m); return $v; }
/** @param mixed $v */
function ocs($v, string $m): string { if (!is_string($v) || trim($v)==='') throw new RuntimeException($m); return $v; }
/** @param mixed $v */
function oci($v, string $m): int { if (!is_int($v)) throw new RuntimeException($m); return $v; }
function oce($v, array $allowed, string $m): string { $v=ocs($v,$m); if (!in_array($v,$allowed,true)) throw new RuntimeException("{$m} Got {$v}."); return $v; }
function ocid(string $v, string $m): string { if (preg_match('/^[a-z0-9][a-z0-9._-]*$/',$v)!==1) throw new RuntimeException($m); return $v; }

$schema = ocj($root.'/config/product/option-contract.schema.json');
if (($schema['$id']??null)!=='https://wpessential.local/schema/product-option-contract.json') throw new RuntimeException('Atomic Option schema $id mismatch.');
$p=oca($schema['properties']??null,'Schema missing properties.'); $d=oca($schema['$defs']??null,'Schema missing $defs.');
if (!isset($p['source_projection'],$d['sourceProjection'],$d['sourceProjectionEntry'])) throw new RuntimeException('Schema missing source_projection provenance.');

$registry=ocj($root.'/config/product/competitor-parity-surfaces.json'); $rows=oca($registry['surfaces']??null,'Registry missing surfaces.');
if(count($rows)!==56) throw new RuntimeException('Registry must contain 56 surfaces.');
$keys=[];$ids=[]; foreach($rows as $r){$r=oca($r,'Invalid registry row.');$id=oci($r['id']??null,'Registry id invalid.');$key=ocs($r['key']??null,'Registry key missing.');if(isset($keys[$id])||isset($ids[$key]))throw new RuntimeException("Duplicate registry {$id}/{$key}.");$keys[$id]=$key;$ids[$key]=$id;}

$progressStatuses=['ATOMIC_INVENTORY_COMPLETE','OPTION_CONTRACT_COMPLETE','UX_CONTRACT_COMPLETE','RUNTIME_CERTIFIED','PRODUCT_PARITY_CERTIFIED']; $prank=array_flip($progressStatuses);
$progress=ocj($root.'/config/product/atomic-option-contract-progress.json'); $prows=oca($progress['surface_status']??null,'Progress missing surface_status.');
if(count($prows)!==56) throw new RuntimeException('Progress must contain 56 rows.');
$ps=[];$truth=['capability_matrix_surfaces'=>56,'atomic_inventory_surfaces'=>0,'option_contract_complete_surfaces'=>0,'ux_contract_complete_surfaces'=>0,'runtime_certified_for_full_parity_contract'=>0,'product_parity_certified_surfaces'=>0];
foreach($prows as $r){$r=oca($r,'Invalid progress row.');$id=oci($r['id']??null,'Progress id invalid.');$key=ocs($r['key']??null,'Progress key missing.');$st=oce($r['status']??null,$progressStatuses,"Invalid progress status {$key}.");if(($keys[$id]??null)!==$key||isset($ps[$key]))throw new RuntimeException("Progress identity mismatch {$id}/{$key}.");$ps[$key]=$st;++$truth['atomic_inventory_surfaces'];if($prank[$st]>=1)++$truth['option_contract_complete_surfaces'];if($prank[$st]>=2)++$truth['ux_contract_complete_surfaces'];if($prank[$st]>=3)++$truth['runtime_certified_for_full_parity_contract'];if($prank[$st]>=4)++$truth['product_parity_certified_surfaces'];}
$decl=oca($progress['truth']??null,'Progress missing truth.');foreach($truth as $k=>$v)if(($decl[$k]??null)!==$v)throw new RuntimeException("Progress truth.{$k} must be {$v}.");

$bank=ocj($root.'/config/product/options-bank-progress.json');$bp=[];foreach(oca($bank['surface_status']??null,'Bank progress missing surface_status.') as $r){$r=oca($r,'Invalid Bank progress row.');$key=ocs($r['key']??null,'Bank key missing.');$bp[$key]=['status'=>ocs($r['status']??null,"Bank status missing {$key}."),'records'=>oci($r['records']??null,"Bank records invalid {$key}.")];}

$ist=['BENCHMARKING','CAPABILITY_INVENTORY_COMPLETE','OPTION_CONTRACT_COMPLETE','UX_CONTRACT_COMPLETE','PRODUCT_PLANNED','IMPLEMENTING','RUNTIME_CERTIFIED','PRODUCT_PARITY_CERTIFIED'];
$ir=['BENCHMARKING'=>0,'CAPABILITY_INVENTORY_COMPLETE'=>0,'OPTION_CONTRACT_COMPLETE'=>1,'UX_CONTRACT_COMPLETE'=>2,'PRODUCT_PLANNED'=>2,'IMPLEMENTING'=>2,'RUNTIME_CERTIFIED'=>3,'PRODUCT_PARITY_CERTIFIED'=>4];
$par=['MISSING','PLANNED_BASELINE','PARITY','EXCEEDS','NOT_APPLICABLE','DEFERRED_WITH_REASON','REJECTED_UNSAFE'];
$sk=['authored_option','user_preference','integration','native_runtime','effective_state','diagnostic','out_of_surface','compatibility_provider','deferred','rejected_unsafe','wpe_exceed'];
$disp=['AUTHORED_ATOMIC','USER_PREFERENCE_ATOMIC','INTEGRATION_ATOMIC','EFFECTIVE_OR_DIAGNOSTIC','RUNTIME_IMPLEMENTATION_EVIDENCE','OUT_OF_SURFACE_REFERENCE','COMPATIBILITY_PROVIDER_MAPPING','DEFERRED','REJECTED_UNSAFE','WPE_EXCEED'];
$dir=$root.'/config/product/option-contracts';$files=is_dir($dir)?glob($dir.'/*.json'):[];if($files===false)throw new RuntimeException('Unable to enumerate instances.');sort($files,SORT_STRING);
$seen=[];$totalOptions=0;$totalProj=0;
foreach($files as $file){
    $in=ocj($file);if(($in['schema_version']??null)!==1)throw new RuntimeException("Unsupported schema_version {$file}.");
    $sid=oci($in['surface_id']??null,"Missing surface_id {$file}.");$skey=ocs($in['surface_key']??null,"Missing surface_key {$file}.");
    if(pathinfo($file,PATHINFO_FILENAME)!==$skey||($keys[$sid]??null)!==$skey||isset($seen[$skey]))throw new RuntimeException("Instance identity mismatch {$skey}.");$seen[$skey]=true;
    $status=oce($in['status']??null,$ist,"Invalid instance status {$skey}.");
    $bench=oca($in['benchmark_snapshot']??null,"Missing benchmark {$skey}.");if(preg_match('/^\d{4}-\d{2}-\d{2}$/',ocs($bench['date']??null,"Missing benchmark date {$skey}."))!==1||oca($bench['products']??null,"Missing benchmark products {$skey}.")===[])throw new RuntimeException("Invalid benchmark {$skey}.");
    $groups=oca($in['feature_groups']??null,"Missing groups {$skey}.");if($groups===[])throw new RuntimeException("Empty groups {$skey}.");$gids=[];$opts=[];
    foreach($groups as $g){$g=oca($g,"Invalid group {$skey}.");$gid=ocid(ocs($g['id']??null,"Group id missing {$skey}."),"Invalid group id {$skey}.");if(isset($gids[$gid]))throw new RuntimeException("Duplicate group {$gid}.");$gids[$gid]=true;ocs($g['label']??null,"Group label missing {$gid}.");foreach(oca($g['options']??null,"Options missing {$gid}.") as $o){$o=oca($o,"Invalid option {$gid}.");$oid=ocid(ocs($o['id']??null,"Option id missing {$gid}."),"Invalid option id {$gid}.");if(isset($opts[$oid]))throw new RuntimeException("Duplicate option {$oid}.");$pst=oce($o['parity_status']??null,$par,"Invalid parity {$oid}.");foreach(['label','kind','requiredness','value_type','default_behavior','ui','validation','storage','runtime','security','portability','testing','competitor_evidence'] as $req)if(!array_key_exists($req,$o))throw new RuntimeException("{$oid} missing {$req}.");if(($o['validation']['server_authoritative']??null)!==true)throw new RuntimeException("{$oid} must be server-authoritative.");if(oca($o['testing']['required_evidence']??null,"Evidence missing {$oid}.")===[])throw new RuntimeException("Evidence empty {$oid}.");if($pst==='REJECTED_UNSAFE'&&($o['security']['class']??null)!=='prohibited')throw new RuntimeException("Unsafe {$oid} must be prohibited.");if($pst==='EXCEEDS'&&!is_array($o['wpe_exceed']??null))throw new RuntimeException("EXCEEDS {$oid} missing wpe_exceed.");$opts[$oid]=$pst;++$totalOptions;}}
    $cov=oca($in['coverage_summary']??null,"Coverage missing {$skey}.");foreach(['atomic_options','parity','exceeds','deferred','rejected_unsafe','missing','unclassified'] as $k)if(oci($cov[$k]??null,"Coverage {$k} invalid {$skey}.")<0)throw new RuntimeException("Coverage {$k} negative.");$cnt=array_count_values(array_values($opts));$exp=['atomic_options'=>count($opts),'parity'=>$cnt['PARITY']??0,'exceeds'=>$cnt['EXCEEDS']??0,'deferred'=>$cnt['DEFERRED_WITH_REASON']??0,'rejected_unsafe'=>$cnt['REJECTED_UNSAFE']??0];foreach($exp as $k=>$v)if($cov[$k]!==$v)throw new RuntimeException("Coverage {$k} {$skey} must be {$v}.");
    $proj=$in['source_projection']??null;if($proj!==null){$proj=oca($proj,"Invalid projection {$skey}.");if(($proj['source_type']??null)!=='options_bank'||($proj['source_surface_key']??null)!==$skey)throw new RuntimeException("Projection identity mismatch {$skey}.");ocs($proj['source_review_version']??null,"Projection review version missing {$skey}.");$sc=oci($proj['source_record_count']??null,"Projection count invalid {$skey}.");$entries=oca($proj['entries']??null,"Projection entries missing {$skey}.");if($sc!==count($entries))throw new RuntimeException("Projection count mismatch {$skey}.");$src=[];foreach($entries as $e){$e=oca($e,"Invalid projection entry {$skey}.");$source=ocs($e['source_id']??null,"source_id missing {$skey}.");if(isset($src[$source]))throw new RuntimeException("Duplicate source {$source}.");$src[$source]=true;oce($e['source_kind']??null,$sk,"Invalid source_kind {$source}.");oce($e['disposition']??null,$disp,"Invalid disposition {$source}.");foreach(oca($e['atomic_ids']??null,"atomic_ids missing {$source}.") as $aid){$aid=ocs($aid,"Invalid atomic id {$source}.");if(!isset($opts[$aid]))throw new RuntimeException("{$source} references missing {$aid}.");}if(!array_key_exists('owner_surface',$e)||(!is_string($e['owner_surface'])&&$e['owner_surface']!==null))throw new RuntimeException("Invalid owner_surface {$source}.");ocs($e['reason']??null,"Reason missing {$source}.");oca($e['evidence_refs']??null,"Evidence refs missing {$source}.");}++$totalProj;if(($bp[$skey]['status']??null)==='BANK_REVIEWED'&&($bp[$skey]['records']??null)!==$sc)throw new RuntimeException("BANK_REVIEWED count mismatch {$skey}.");}
    $rank=$ir[$status];if($rank>=1&&(($cov['missing']??null)!==0||($cov['unclassified']??null)!==0))throw new RuntimeException("{$skey} {$status} has missing/unclassified.");if($rank>=1&&($bp[$skey]['status']??null)==='BANK_REVIEWED'&&$proj===null)throw new RuntimeException("BANK_REVIEWED {$skey} requires projection.");if($prank[$ps[$skey]]>$rank)throw new RuntimeException("Progress {$skey} outruns instance {$status}.");
}
printf("Atomic Option contracts: PASS (%d instance(s), %d option(s), %d projection(s); progress %d/56 option-complete, %d/56 UX-complete).\n",count($files),$totalOptions,$totalProj,$truth['option_contract_complete_surfaces'],$truth['ux_contract_complete_surfaces']);
