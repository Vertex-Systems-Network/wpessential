<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Throwable;

final class FieldValueNormalizer
{
    /**
     * Normalize a submitted value against one normalized Field definition.
     *
     * This is deliberately fail-closed. Types that require an owning engine/provider
     * (for example Relations) are rejected until their adapter is explicitly wired.
     *
     * @param array<string,mixed> $field
     */
    public function normalize(array $field, mixed $value): mixed
    {
        $this->assertNormalizedField($field);

        if ($field['stores_value'] === false) {
            if ($value !== null && $value !== '' && $value !== []) {
                throw new InvalidArgumentException(sprintf('UI-only field "%s" cannot persist a value.', $field['key']));
            }
            return null;
        }

        $settings = $field['settings'];
        $required = $this->boolSetting($settings, 'required', false);
        if ($value === null || $value === '') {
            if ($required) {
                throw new InvalidArgumentException(sprintf('Field "%s" is required.', $field['key']));
            }
            return null;
        }

        $repeatability = $field['repeatability'];
        if (($repeatability['mode'] ?? null) !== 'container_managed' && ($repeatability['enabled'] ?? false) === true) {
            if (!is_array($value) || !array_is_list($value)) {
                throw new InvalidArgumentException(sprintf('Repeatable field "%s" requires a list value.', $field['key']));
            }
            $this->assertItemCount($field, $value, $repeatability);
            return array_map(fn (mixed $item): mixed => $this->normalizeSingle($field, $item), $value);
        }

        return $this->normalizeSingle($field, $value);
    }

    /** @param array<string,mixed> $field */
    private function normalizeSingle(array $field, mixed $value): mixed
    {
        $type = $field['type'];
        if (!is_string($type)) {
            throw new InvalidArgumentException('Normalized field type must be a string.');
        }

        return match ($type) {
            'text', 'textarea', 'wysiwyg', 'block_editor', 'code_editor', 'markdown', 'phone' => $this->stringValue($field, $value),
            'email' => $this->emailValue($field, $value),
            'url', 'oembed' => $this->urlValue($field, $value),
            'number', 'range' => $this->numberValue($field, $value),
            'true_false', 'switcher', 'checkbox' => $this->booleanValue($field, $value),
            'checkbox_list', 'text_list' => $this->scalarListValue($field, $value),
            'radio', 'button_group', 'select', 'select_advanced', 'autocomplete', 'combobox' => $this->choiceValue($field, $value),
            'date' => $this->dateValue($field, $value),
            'time' => $this->timeValue($field, $value),
            'datetime' => $this->dateTimeValue($field, $value),
            'color' => $this->colorValue($field, $value),
            'image', 'file', 'media', 'video' => $this->positiveIntegerReference($field, $value),
            'gallery', 'file_advanced' => $this->positiveIntegerReferenceList($field, $value),
            'post_object' => $this->positiveIntegerReference($field, $value),
            'posts' => $this->positiveIntegerReferenceList($field, $value),
            'taxonomy', 'user' => $this->wordpressEntityValue($field, $value),
            'nav_menu', 'sidebar' => $this->machineReference($field, $value),
            'group' => $this->groupValue($field, $value),
            'repeater' => $this->repeaterValue($field, $value),
            'relationship' => throw new InvalidArgumentException('Relationship values are owned by the Relations Engine and cannot be normalized as ordinary field meta.'),
            default => throw new InvalidArgumentException(sprintf('Field value normalization for type "%s" is not implemented yet.', $type)),
        };
    }

    /** @param array<string,mixed> $field */
    private function stringValue(array $field, mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a string value.', $field['key']));
        }
        $settings = $field['settings'];
        $trim = $this->boolSetting($settings, 'trim', in_array($field['type'], ['text', 'email', 'url'], true));
        $value = $trim ? trim($value) : $value;
        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        $min = $this->optionalNonNegativeInt($settings['min_length'] ?? null, 'min_length');
        $max = $this->optionalNonNegativeInt($settings['max_length'] ?? ($settings['maxlength'] ?? null), 'max_length');
        if ($min !== null && $length < $min) {
            throw new InvalidArgumentException(sprintf('Field "%s" is shorter than its minimum length.', $field['key']));
        }
        if ($max !== null && $length > $max) {
            throw new InvalidArgumentException(sprintf('Field "%s" exceeds its maximum length.', $field['key']));
        }
        return $value;
    }

    /** @param array<string,mixed> $field */
    private function emailValue(array $field, mixed $value): string
    {
        $email = $this->stringValue($field, $value);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a valid email address.', $field['key']));
        }
        return $email;
    }

    /** @param array<string,mixed> $field */
    private function urlValue(array $field, mixed $value): string
    {
        $url = $this->stringValue($field, $value);
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a valid URL.', $field['key']));
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $allowed = $field['settings']['allowed_schemes'] ?? ['http', 'https'];
        if (!is_array($allowed) || !array_is_list($allowed) || !in_array($scheme, $allowed, true)) {
            throw new InvalidArgumentException(sprintf('Field "%s" URL scheme is not allowed.', $field['key']));
        }
        return $url;
    }

    /** @param array<string,mixed> $field */
    private function numberValue(array $field, mixed $value): int|float
    {
        if (!is_int($value) && !is_float($value) && !(is_string($value) && is_numeric($value))) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a numeric value.', $field['key']));
        }
        $settings = $field['settings'];
        $integer = $this->boolSetting($settings, 'integer', false);
        if ($integer) {
            if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                throw new InvalidArgumentException(sprintf('Field "%s" requires an integer value.', $field['key']));
            }
            $normalized = (int) $value;
        } else {
            $normalized = is_int($value) ? $value : (float) $value;
        }
        $min = $this->optionalNumber($settings['min'] ?? null, 'min');
        $max = $this->optionalNumber($settings['max'] ?? null, 'max');
        if ($min !== null && $normalized < $min) {
            throw new InvalidArgumentException(sprintf('Field "%s" is below its minimum.', $field['key']));
        }
        if ($max !== null && $normalized > $max) {
            throw new InvalidArgumentException(sprintf('Field "%s" exceeds its maximum.', $field['key']));
        }
        return $normalized;
    }

    /** @param array<string,mixed> $field */
    private function booleanValue(array $field, mixed $value): bool
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a boolean value.', $field['key']));
        }
        return $value;
    }

    /** @param array<string,mixed> $field @return list<int|float|string|bool> */
    private function scalarListValue(array $field, mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a list.', $field['key']));
        }
        $normalized = [];
        foreach ($value as $item) {
            $normalized[] = $this->scalar($field, $item);
        }
        $this->assertChoiceMembership($field, $normalized);
        return $normalized;
    }

    /** @param array<string,mixed> $field */
    private function choiceValue(array $field, mixed $value): mixed
    {
        $multiple = $this->boolSetting($field['settings'], 'multiple', false);
        if ($multiple) {
            return $this->scalarListValue($field, $value);
        }
        $normalized = $this->scalar($field, $value);
        $this->assertChoiceMembership($field, [$normalized]);
        return $normalized;
    }

    /** @param array<string,mixed> $field */
    private function scalar(array $field, mixed $value): int|float|string|bool
    {
        if (!is_int($value) && !is_float($value) && !is_string($value) && !is_bool($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" accepts scalar choices only.', $field['key']));
        }
        return $value;
    }

    /** @param array<string,mixed> $field @param list<int|float|string|bool> $values */
    private function assertChoiceMembership(array $field, array $values): void
    {
        $choices = $field['settings']['choices'] ?? null;
        if ($choices === null) {
            return;
        }
        if (!is_array($choices)) {
            throw new InvalidArgumentException('choices must be a list or named map.');
        }
        $allowed = array_is_list($choices) ? $choices : array_keys($choices);
        foreach ($values as $value) {
            if (!in_array($value, $allowed, true)) {
                throw new InvalidArgumentException(sprintf('Field "%s" contains a value outside its allowed choices.', $field['key']));
            }
        }
    }

    /** @param array<string,mixed> $field */
    private function dateValue(array $field, mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a date string.', $field['key']));
        }
        $mode = $field['settings']['precision'] ?? ($field['settings']['control_mode'] ?? 'day');
        if ($mode === 'week') {
            if (preg_match('/^\d{4}-W(?:0[1-9]|[1-4]\d|5[0-3])$/', $value) !== 1) {
                throw new InvalidArgumentException(sprintf('Field "%s" requires an ISO week value.', $field['key']));
            }
            return $value;
        }
        if ($mode === 'month') {
            if (preg_match('/^\d{4}-(?:0[1-9]|1[0-2])$/', $value) !== 1) {
                throw new InvalidArgumentException(sprintf('Field "%s" requires a YYYY-MM month value.', $field['key']));
            }
            return $value;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date instanceof DateTimeImmutable || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a valid YYYY-MM-DD date.', $field['key']));
        }
        return $value;
    }

    /** @param array<string,mixed> $field */
    private function timeValue(array $field, mixed $value): string
    {
        if (!is_string($value) || preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires HH:MM or HH:MM:SS.', $field['key']));
        }
        return $value;
    }

    /** @param array<string,mixed> $field */
    private function dateTimeValue(array $field, mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires an RFC3339 datetime.', $field['key']));
        }
        try {
            $instant = new DateTimeImmutable($value);
        } catch (Throwable) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires an RFC3339 datetime.', $field['key']));
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?(?:Z|[+-]\d{2}:\d{2})$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires an explicit timezone offset.', $field['key']));
        }
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    }

    /** @param array<string,mixed> $field */
    private function colorValue(array $field, mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a color string.', $field['key']));
        }
        $value = strtolower(trim($value));
        $valid = preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/', $value) === 1
            || $this->validRgbColor($value)
            || $this->validHslColor($value);
        if (!$valid) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a supported HEX/RGB/RGBA/HSL/HSLA color.', $field['key']));
        }
        return $value;
    }

    private function validRgbColor(string $value): bool
    {
        if (preg_match('/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*(0(?:\.\d+)?|1(?:\.0+)?))?\s*\)$/', $value, $matches) !== 1) {
            return false;
        }
        if (str_starts_with($value, 'rgba(') && !isset($matches[4])) {
            return false;
        }
        return (int) $matches[1] <= 255 && (int) $matches[2] <= 255 && (int) $matches[3] <= 255;
    }

    private function validHslColor(string $value): bool
    {
        if (preg_match('/^hsla?\(\s*(-?\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)%\s*,\s*(\d+(?:\.\d+)?)%(?:\s*,\s*(0(?:\.\d+)?|1(?:\.0+)?))?\s*\)$/', $value, $matches) !== 1) {
            return false;
        }
        if (str_starts_with($value, 'hsla(') && !isset($matches[4])) {
            return false;
        }
        return (float) $matches[2] <= 100.0 && (float) $matches[3] <= 100.0;
    }

    /** @param array<string,mixed> $field */
    private function positiveIntegerReference(array $field, mixed $value): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a positive WordPress object ID.', $field['key']));
        }
        return (int) $value;
    }

    /** @param array<string,mixed> $field @return list<int> */
    private function positiveIntegerReferenceList(array $field, mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a list of WordPress object IDs.', $field['key']));
        }
        return array_map(fn (mixed $item): int => $this->positiveIntegerReference($field, $item), $value);
    }

    /** @param array<string,mixed> $field */
    private function wordpressEntityValue(array $field, mixed $value): int|array
    {
        if ($this->boolSetting($field['settings'], 'multiple', false)) {
            return $this->positiveIntegerReferenceList($field, $value);
        }
        return $this->positiveIntegerReference($field, $value);
    }

    /** @param array<string,mixed> $field */
    private function machineReference(array $field, mixed $value): string
    {
        if (!is_string($value) || preg_match('/^[a-z0-9][a-z0-9_.:-]{0,127}$/i', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('Field "%s" requires a safe registry reference.', $field['key']));
        }
        return $value;
    }

    /** @param array<string,mixed> $field @return array<string,mixed> */
    private function groupValue(array $field, mixed $value): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException(sprintf('Group field "%s" requires an object/map.', $field['key']));
        }
        $subfields = $field['subfields'];
        if (!is_array($subfields) || !array_is_list($subfields)) {
            throw new InvalidArgumentException('Normalized group subfields must be a list.');
        }
        return $this->normalizeRow($field, $subfields, $value);
    }

    /** @param array<string,mixed> $field @return list<array<string,mixed>> */
    private function repeaterValue(array $field, mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException(sprintf('Repeater field "%s" requires a list of rows.', $field['key']));
        }
        $subfields = $field['subfields'];
        if (!is_array($subfields) || !array_is_list($subfields)) {
            throw new InvalidArgumentException('Normalized repeater subfields must be a list.');
        }
        $min = $this->optionalNonNegativeInt($field['settings']['min_rows'] ?? null, 'min_rows');
        $max = $this->optionalNonNegativeInt($field['settings']['max_rows'] ?? null, 'max_rows');
        if ($min !== null && count($value) < $min) {
            throw new InvalidArgumentException(sprintf('Repeater field "%s" has too few rows.', $field['key']));
        }
        if ($max !== null && count($value) > $max) {
            throw new InvalidArgumentException(sprintf('Repeater field "%s" has too many rows.', $field['key']));
        }
        $rows = [];
        foreach ($value as $row) {
            if (!is_array($row) || array_is_list($row)) {
                throw new InvalidArgumentException(sprintf('Repeater field "%s" rows must be objects/maps.', $field['key']));
            }
            $rows[] = $this->normalizeRow($field, $subfields, $row);
        }
        return $rows;
    }

    /**
     * @param array<string,mixed> $container
     * @param list<array<string,mixed>> $subfields
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function normalizeRow(array $container, array $subfields, array $row): array
    {
        $known = [];
        $normalized = [];
        foreach ($subfields as $subfield) {
            $key = $subfield['key'] ?? null;
            if (!is_string($key)) {
                throw new InvalidArgumentException('Normalized subfield key must be a string.');
            }
            $known[$key] = true;
            $normalized[$key] = $this->normalize($subfield, $row[$key] ?? null);
        }
        foreach (array_keys($row) as $key) {
            if (!is_string($key) || !isset($known[$key])) {
                throw new InvalidArgumentException(sprintf('Container field "%s" received an unknown subfield.', $container['key']));
            }
        }
        return $normalized;
    }

    /** @param array<string,mixed> $field @param list<mixed> $items @param array<string,mixed> $repeatability */
    private function assertItemCount(array $field, array $items, array $repeatability): void
    {
        $count = count($items);
        $min = $repeatability['min'] ?? 0;
        $max = $repeatability['max'] ?? null;
        if (is_int($min) && $count < $min) {
            throw new InvalidArgumentException(sprintf('Repeatable field "%s" has too few values.', $field['key']));
        }
        if (is_int($max) && $count > $max) {
            throw new InvalidArgumentException(sprintf('Repeatable field "%s" has too many values.', $field['key']));
        }
    }

    /** @param array<string,mixed> $field */
    private function assertNormalizedField(array $field): void
    {
        foreach (['key', 'type', 'stores_value', 'settings', 'repeatability', 'subfields'] as $required) {
            if (!array_key_exists($required, $field)) {
                throw new InvalidArgumentException(sprintf('Field definition is not normalized; missing "%s".', $required));
            }
        }
        if (!is_string($field['key']) || !is_string($field['type']) || !is_bool($field['stores_value'])
            || !is_array($field['settings']) || array_is_list($field['settings'])
            || !is_array($field['repeatability']) || array_is_list($field['repeatability'])
            || !is_array($field['subfields']) || !array_is_list($field['subfields'])
        ) {
            throw new InvalidArgumentException('Field definition has an invalid normalized shape.');
        }
    }

    /** @param array<string,mixed> $settings */
    private function boolSetting(array $settings, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $settings)) {
            return $default;
        }
        if (!is_bool($settings[$key])) {
            throw new InvalidArgumentException(sprintf('Field setting "%s" must be boolean.', $key));
        }
        return $settings[$key];
    }

    private function optionalNonNegativeInt(mixed $value, string $label): ?int
    {
        if ($value === null) {
            return null;
        }
        if (!is_int($value) || $value < 0) {
            throw new InvalidArgumentException(sprintf('%s must be a non-negative integer.', $label));
        }
        return $value;
    }

    private function optionalNumber(mixed $value, string $label): int|float|null
    {
        if ($value === null) {
            return null;
        }
        if (!is_int($value) && !is_float($value)) {
            throw new InvalidArgumentException(sprintf('%s must be numeric.', $label));
        }
        return $value;
    }
}
