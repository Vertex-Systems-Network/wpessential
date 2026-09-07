<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Definition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class ListingRenderBinding
{
    public function __construct(
        public string $kind,
        public string $bindingKey,
        public string $expectedType,
        public ?string $queryFieldRef = null,
        public ?string $sourceRef = null,
        public ?string $valueRef = null,
        public ?string $resourceType = null,
        public ?string $resourceIdFieldRef = null,
    ) {
        if (!in_array($this->kind, ['query_field', 'dynamic_value'], true)) {
            throw new InvalidArgumentException('Listing render binding kind is unsupported.');
        }
        $this->assertSemantic($this->bindingKey, 'Listing render binding key');
        if (!in_array($this->expectedType, ['string', 'int', 'float', 'bool', 'string_list', 'int_list'], true)) {
            throw new InvalidArgumentException('Listing render binding expected type is unsupported.');
        }

        if ($this->kind === 'query_field') {
            if ($this->queryFieldRef === null) {
                throw new InvalidArgumentException('Query-field binding requires an explicit Query field reference.');
            }
            $this->assertSemantic($this->queryFieldRef, 'Listing Query field reference');
            if ($this->sourceRef !== null || $this->valueRef !== null || $this->resourceType !== null || $this->resourceIdFieldRef !== null) {
                throw new InvalidArgumentException('Query-field binding cannot carry Dynamic Value metadata.');
            }
            return;
        }

        if ($this->queryFieldRef !== null || $this->sourceRef === null || $this->valueRef === null || $this->resourceType === null || $this->resourceIdFieldRef === null) {
            throw new InvalidArgumentException('Dynamic-value binding requires explicit source, value, resource type and resource-id field references.');
        }
        $this->assertSemantic($this->sourceRef, 'Dynamic Value source reference');
        $this->assertSemantic($this->valueRef, 'Dynamic Value value reference');
        $this->assertSemantic($this->resourceType, 'Dynamic Value resource type');
        $this->assertSemantic($this->resourceIdFieldRef, 'Dynamic Value resource-id field reference');
    }

    /** @return array<string,string> */
    public function fingerprintData(): array
    {
        $data = [
            'kind' => $this->kind,
            'binding_key' => $this->bindingKey,
            'expected_type' => $this->expectedType,
        ];
        foreach ([
            'query_field_ref' => $this->queryFieldRef,
            'source_ref' => $this->sourceRef,
            'value_ref' => $this->valueRef,
            'resource_type' => $this->resourceType,
            'resource_id_field_ref' => $this->resourceIdFieldRef,
        ] as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    private function assertSemantic(string $value, string $label): void
    {
        if (!preg_match('/^[a-z][a-z0-9._:-]{0,159}$/', $value)) {
            throw new InvalidArgumentException($label . ' must be a bounded semantic reference.');
        }
    }
}
