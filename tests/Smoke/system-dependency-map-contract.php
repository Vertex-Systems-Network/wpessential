<?php
/**
 * Every system has a known place in the dependency map: requires form a DAG rooted at platform,
 * links point at real surfaces, and each competitor capability has one owner.
 */
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
}

$root     = dirname( __DIR__, 2 ) . '/config/product/';
$map      = json_decode( (string) file_get_contents( $root . 'system-dependency-map.json' ), true, 512, JSON_THROW_ON_ERROR );
$surfaces = json_decode( (string) file_get_contents( $root . 'competitor-parity-surfaces.json' ), true, 512, JSON_THROW_ON_ERROR );
$errors   = [];

$expected = array_column( $surfaces['surfaces'], 'key' );
$graph    = [];
foreach ( $map['surfaces'] as $surface ) {
	if ( isset( $graph[ $surface['key'] ] ) ) {
		$errors[] = "duplicate surface {$surface['key']}";
	}
	$graph[ $surface['key'] ] = $surface['requires'];
}
foreach ( array_diff( $expected, array_keys( $graph ) ) as $missing ) {
	$errors[] = "surface {$missing} is not mapped";
}
foreach ( array_diff( array_keys( $graph ), $expected ) as $extra ) {
	$errors[] = "surface {$extra} is not a parity surface";
}
foreach ( $map['surfaces'] as $surface ) {
	foreach ( array_merge( $surface['requires'], $surface['attaches_from'] ) as $ref ) {
		if ( ! isset( $graph[ $ref ] ) ) {
			$errors[] = "{$surface['key']} links unknown surface {$ref}";
		}
	}
	if ( $surface['key'] !== 'platform' && $surface['requires'] === [] ) {
		$errors[] = "{$surface['key']} must require at least platform";
	}
}

// Depth-first cycle check over requires.
$state = [];
$visit = static function ( string $key, array $path ) use ( &$visit, &$state, $graph, &$errors ): void {
	if ( ( $state[ $key ] ?? 0 ) === 2 ) {
		return;
	}
	if ( ( $state[ $key ] ?? 0 ) === 1 ) {
		$errors[] = 'requires cycle: ' . implode( ' -> ', [ ...$path, $key ] );
		return;
	}
	$state[ $key ] = 1;
	foreach ( $graph[ $key ] ?? [] as $next ) {
		$visit( $next, [ ...$path, $key ] );
	}
	$state[ $key ] = 2;
};
foreach ( array_keys( $graph ) as $key ) {
	$visit( $key, [] );
}

$seen = [];
foreach ( $map['jetengine_capability_owners'] as $row ) {
	if ( isset( $seen[ $row['capability'] ] ) ) {
		$errors[] = "capability {$row['capability']} has two owners";
	}
	$seen[ $row['capability'] ] = true;
	if ( ! isset( $graph[ $row['owner'] ] ) ) {
		$errors[] = "capability {$row['capability']} owned by unknown surface {$row['owner']}";
	}
}

if ( $errors !== [] ) {
	fwrite( STDERR, implode( PHP_EOL, $errors ) . PHP_EOL );
	exit( 1 );
}
echo 'system-dependency-map: ' . count( $graph ) . ' surfaces, ' . count( $seen ) . " capabilities OK\n";
