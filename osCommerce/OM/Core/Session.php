<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

class Session
{
    protected static $driver;
    private static $default_driver = 'File';

    public static function load(string $name = null)
    {
        if (!isset(static::$driver)) {
            static::$driver = OSCOM::configExists('store_sessions') ? OSCOM::getConfig('store_sessions') : static::$default_driver;
        }

        if (!class_exists(__NAMESPACE__ . '\\Session\\' . static::$driver)) {
            trigger_error('OSCOM\Session::load(): Driver "' . static::$driver . '" does not exist, using default "' . static::$default_driver . '"', E_USER_ERROR);

            static::$driver = static::$default_driver;
        }

        $class_name = __NAMESPACE__ . '\\Session\\' . static::$driver;

        $obj = new $class_name();

        if (!isset($name)) {
            $name = 'sid';
        }

        $obj->setName($name);
        $obj->setLifeTime(ini_get('session.gc_maxlifetime'));

        return $obj;
    }
}
