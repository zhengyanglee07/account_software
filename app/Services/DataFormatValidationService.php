<?php

namespace App\Services;

use Auth;

class DataFormatValidationService
{
    /**
     * Validations for imported column data
     *
     * This method will directly throw error instead of returning validation result
     *
     * @param $field
     * @param $columnValue
     * @param string $columnName
     */
    public function columnDataValidation($field, $columnValue, $columnName = ''): void
    {
        $generateErrMsg = static function ($type) use ($field, $columnName) {
            $column = $columnName ?: $field;
            return "$type format in $column column is invalid for some data. Please correct your data and import again.";
        };

        if ($field === "email" && !$this->validateEmail($columnValue)) {
            abort(422, $generateErrMsg('Email'));
        }

        if ($field === 'zip' && !$this->validateZip($columnValue)) {
            abort(422, $generateErrMsg('Postcode'));
        }

        if ($field === 'phone_number' && !$this->validatePhone($columnValue)) {
            abort(422, $generateErrMsg('Phone number'));
        }

        // custom fields
        if ($field === 'datetime' && !$this->validateDateTime($columnValue)) {
            abort(422, $generateErrMsg('Datetime'));
        }

        if ($field === 'date' && !$this->validateDate($columnValue)) {
            abort(422, $generateErrMsg('Date'));
        }

        if ($field === 'number' && !$this->validateNumber($columnValue)) {
            abort(422, $generateErrMsg('Number'));
        }
    }

    /**
     * This method validates value related to custom field types only.
     * Currently it has date, datetime, number, email and text
     *
     * @param $customFieldType
     * @param $value
     * @return false|int
     */
    public function validateCustomField($customFieldType, $value)
    {
        if ($customFieldType === 'date') {
            return $this->validateDate($value);
        }

        if ($customFieldType === 'datetime') {
            return $this->validateDateTime($value);
        }

        if ($customFieldType === 'number') {
            return $this->validateNumber($value);
        }

        // emailAddress here to provide backward-compatible support
        if ($customFieldType === 'emailAddress' || $customFieldType === 'email') {
            return $this->validateEmail($value);
        }

        // accepting any input for "text" type. null/empty custom field
        // type might occurs for legacy custom field name
        if ($customFieldType === 'text' || !$customFieldType) {
            return 1;
        }

        \Log::error('Incorrect custom field type', [
            'customFieldType' => $customFieldType,
            'value' => $value,
            'user_id' => Auth::user()->id ?? null
        ]);

        return 0;
    }

    /**
     * ref: https://emailregex.com/
     * 99.99% works as claimed by the site
     *
     * Note: also matches empty email
     *
     * @param $value
     * @return false|int
     */
    public function validateEmail($value)
    {
        return preg_match(
            '/^$|^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD',
            $value
        );
    }

    /**
     * currently only cater for 5-digit zip, maybe you'd want to add more options,
     * if your boss ask you to support different country zip
     *
     * Note: also matches empty zip
     *
     * @param $value
     * @return false|int
     */
    public function validateZip($value)
    {
        return preg_match('/^$|\b\d{1,10}\b/', $value);
    }

    /**
     * ref: https://stackoverflow.com/a/39331613
     * As claimed by SO author, this regex seems to match lots of phone numbers in
     * different countries
     *
     * @param $value
     * @return false|int
     */
    public function validatePhone($value)
    {
        return preg_match(
            '%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i',
            $value
        );
    }

    /**
     * Matches datetime with laravel default format (Y-m-d H:i:s)
     *
     * Note: you might wan to modify this regex later depends on what format you want
     *
     * @param $value
     * @return false|int
     */
    public function validateDateTime($value)
    {
        return preg_match(
            '/^$|^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/',
            $value
        );
    }

    /**
     * Matches date
     *
     * Note: you might wan to modify this regex later depends on what format you want
     *
     * @param $value
     * @return false|int
     */
    public function validateDate($value)
    {
        return preg_match(
            '/^$|^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/',
            $value
        );
    }

    /**
     * Simple validation that matches digits
     *
     * @param $value
     * @return false|int
     */
    public function validateNumber($value)
    {
        return preg_match('/^[0-9]*$/', $value);
    }
}