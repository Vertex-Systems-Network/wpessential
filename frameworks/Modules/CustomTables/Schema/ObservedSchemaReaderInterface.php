<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Schema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;

interface ObservedSchemaReaderInterface {

	public function observe( TableSchemaDescriptor $descriptor ): ObservedTableSchema;
}
