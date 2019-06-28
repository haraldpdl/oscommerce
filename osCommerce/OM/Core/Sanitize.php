<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

class Sanitize
{
    public static function simple(?string $value): string
    {
        if (!isset($value)) {
            return '';
        }

        return preg_replace([
            '/\R/',
            '/ {2,}/',
            '/[<>]/'
        ], [
            '',
            ' ',
            '_'
        ], trim($value)) ?? '';
    }

    public static function para(?string $value): string
    {
        if (!isset($value)) {
            return '';
        }

        return preg_replace([
            '/\R{2,}/',
            '/ {2,}/',
            '/[<>]/'
        ], [
            "\n\n",
            ' ',
            '_'
        ], trim($value)) ?? '';
    }

    public static function password(?string $value): string
    {
        if (!isset($value)) {
            return '';
        }

        return preg_replace('/\R/', '', $value) ?? '';
    }
}
