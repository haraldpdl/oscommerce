<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Hash;

class Phpass implements \osCommerce\OM\Core\HashInterface
{
    protected static $phpass;

    public static function get(string $string): string
    {
        if (!isset(static::$phpass)) {
            static::$phpass = new \PasswordHash(10, true);
        }

        return static::$phpass->HashPassword($string);
    }

    public static function validate(string $plain, string $hashed): bool
    {
        if (!isset(static::$phpass)) {
            static::$phpass = new \PasswordHash(10, true);
        }

        return static::$phpass->CheckPassword($plain, $hashed);
    }

    public static function canValidate(string $hash): bool
    {
        if (substr($hash, 0, 3) === '$P$') {
            return true;
        }

        return false;
    }

    public static function canUse(): bool
    {
        return class_exists('\PasswordHash');
    }
}
