<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Hash;

class Salt implements \osCommerce\OM\Core\HashInterface
{
    public static function get(string $string): string
    {
        $hash = '';

        for ($i = 0; $i < 10; $i++) {
            $hash .= random_int(PHP_INT_MIN, PHP_INT_MAX);
        }

        $salt = substr(md5($hash), 0, 2);

        $hash = md5($salt . $string) . ':' . $salt;

        return $hash;
    }

    public static function validate(string $plain, string $hashed): bool
    {
        if (empty($plain) || empty($hashed)) {
            return false;
        }

// split apart the hash / salt
        $stack = explode(':', $hashed);

        if (count($stack) != 2) {
            return false;
        }

        return (md5($stack[1] . $plain) == $stack[0]);
    }

    public static function canValidate(string $hash): bool
    {
        if (preg_match('/^[A-Z0-9]{32}\:[A-Z0-9]{2}$/i', $hash) === 1) {
            return true;
        }

        return false;
    }

    public static function canUse(): bool
    {
        return true;
    }
}
