<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

use osCommerce\OM\Core\{
    DirectoryListing,
    OSCOM
};

class Hash
{
    public static function get(string $string, string $driver = null): string
    {
        if (isset($driver)) {
            return call_user_func(['osCommerce\\OM\\Core\\Hash\\' . $driver, 'get'], $string);
        }

        return password_hash($string, PASSWORD_DEFAULT);
    }

    public static function validate(string $plain, string $hash, string $driver = null): bool
    {
        if (!isset($driver)) {
            $type = static::getType($hash);

            if (!is_null($type) && class_exists('osCommerce\\OM\\Core\\Hash\\' . $type)) {
                $driver = $type;
            }
        }

        if (isset($driver)) {
            return call_user_func(['osCommerce\\OM\\Core\\Hash\\' . $driver, 'validate'], $plain, $hash);
        }

        return password_verify($plain, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        $info = password_get_info($hash);

        if ($info['algo'] < 1) { // Hash not produced with password_hash()
            return true;
        }

        return password_needs_rehash($hash, $info['algo']);
    }

    public static function getType(string $hash): ?string
    {
        $info = password_get_info($hash);

        if ($info['algo'] > 0) {
            return $info['algoName'];
        }

        $DL = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Core/Hash/');
        $DL->setIncludeDirectories(false);
        $DL->setCheckExtension('php');

        foreach ($DL->getFiles() as $file) {
            $driver = basename($file['name'], '.php');

            if (is_subclass_of('osCommerce\\OM\\Core\\Hash\\' . $driver, 'osCommerce\\OM\\Core\\HashInterface')) {
                if (call_user_func(['osCommerce\\OM\\Core\\Hash\\' . $driver, 'canValidate'], $hash)) {
                    return $driver;
                }
            }
        }

        trigger_error('osCommerce\\OM\\Core\\Hash::getType() hash type not found for "' . substr($hash, 0, 5) . '"');

        return null;
    }

    public static function getRandomString(int $length, string $type = 'mixed'): string
    {
        if (!in_array($type, ['mixed', 'chars', 'digits'])) {
            trigger_error('osCommerce\\OM\\Core\\Hash::getRandomString() $type not recognized:' . $type, E_USER_ERROR);

            return '';
        }

        if ($length < 1) {
            trigger_error('osCommerce\\OM\\Core\\Hash::getRandomString() $length must be 1 or higher value', E_USER_ERROR);

            return '';
        }

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';

        $base = '';

        if (($type == 'mixed') || ($type == 'chars')) {
            $base .= $chars;
        }

        if (($type == 'mixed') || ($type == 'digits')) {
            $base .= $digits;
        }

        $base_length = strlen($base) - 1;

        $rand_value = '';

        for ($i = 0; $i < $length; $i++) {
            $rand_value .= $base[random_int(0, $base_length)];
        }

        return $rand_value;
    }
}
