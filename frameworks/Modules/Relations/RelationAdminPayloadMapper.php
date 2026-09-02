<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class RelationAdminPayloadMapper
{
    /** @param array<string,string> $input @return array<string,mixed> */
    public function map(array $input): array
    {
        return [
            'relation_key' => $this->required($input, 'relation_key'),
            'title' => $this->required($input, 'title'),
            'description' => $input['description'] ?? '',
            'cardinality' => $this->required($input, 'cardinality'),
            'direction' => [
                'reciprocal' => ($input['reciprocal'] ?? '0') === '1',
                'bidirectional_traversal' => ($input['bidirectional_traversal'] ?? '0') === '1',
            ],
            'from' => $this->endpoint($input, 'from'),
            'to' => $this->endpoint($input, 'to'),
            'bounds' => [
                'from_min' => $this->nonNegative($input, 'from_min'),
                'from_max' => $this->nullablePositive($input, 'from_max'),
                'to_min' => $this->nonNegative($input, 'to_min'),
                'to_max' => $this->nullablePositive($input, 'to_max'),
            ],
            'unique_edge' => ($input['unique_edge'] ?? '0') === '1',
        ];
    }

    /** @param array<string,string> $input @return array{object_type:string,object_subtype:?string,label:string} */
    private function endpoint(array $input, string $side): array
    {
        $type = $this->required($input, $side . '_type');
        $subtype = $input[$side . '_subtype'] ?? '';
        $label = $this->required($input, $side . '_label');

        return [
            'object_type' => $type,
            'object_subtype' => in_array($type, ['post', 'term'], true) && $subtype !== '' ? $subtype : null,
            'label' => $label,
        ];
    }

    /** @param array<string,string> $input */
    private function required(array $input, string $key): string
    {
        $value = trim($input[$key] ?? '');
        if ($value === '') {
            throw new InvalidArgumentException(sprintf('Relation admin field "%s" is required.', $key));
        }
        return $value;
    }

    /** @param array<string,string> $input */
    private function nonNegative(array $input, string $key): int
    {
        $value = trim($input[$key] ?? '0');
        if ($value === '' || preg_match('/^\d+$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('Relation admin field "%s" must be a non-negative integer.', $key));
        }
        return (int) $value;
    }

    /** @param array<string,string> $input */
    private function nullablePositive(array $input, string $key): ?int
    {
        $value = trim($input[$key] ?? '');
        if ($value === '') {
            return null;
        }
        if (preg_match('/^[1-9]\d*$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('Relation admin field "%s" must be blank or a positive integer.', $key));
        }
        return (int) $value;
    }
}
