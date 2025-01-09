<?php

namespace App\Utils\SegmentFormatters;

class CustomFieldFormatter extends BaseFormatter implements IFormatter
{
    /**
     * @inheritDoc
     *
     * Special note on this:
     * The incoming subCondition will have the following structure:
     * [
     *   {
     *     key: 'custom_field_name',
     *     value: 'custom-field-a',
     *   },
     *   {
     *     key: is/is not/is set/is not set/...,
     *     value: value for the custom field
     *   }
     * ]
     *
     * So the logic inside this method is specifically aiming the first
     * condition in subCondition, by replacing the key 'custom_field_name'
     * with its corresponding value. To make this situation clearer:
     *
     * Without replacement: Custom Field custom_field_name custom-field-a is something
     * With replacement: Custom Field custom-field-a is something
     */
    public function format(array $subCondition): string
    {
        // simply remove the key 'custom_field_name' if present
        $formattedKey = $subCondition['key'] === 'custom_field_name'
            ? ''
            : $subCondition['key'];

        return $formattedKey . ' ' . $subCondition['value'] . ' ';
    }
}
