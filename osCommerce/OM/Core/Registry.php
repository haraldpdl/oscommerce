<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

class Registry
{
    protected static $aliases = [];
    protected static $data = [];

    public static function get(string $key)
    {
        if (static::exists($key)) {
            $value = static::$data[$key];
        } else {
            $registry_class = null;

            if (array_key_exists($key, static::$aliases)) {
                $registry_class = 'osCommerce\\OM\\' . static::$aliases[$key];
            } else {
                $bt = debug_backtrace(0, 2);

                $class = $bt[1]['class'];

                $ns_array = explode('\\', $class);

                if (count($ns_array) > 5) {
                    if (implode('\\', array_slice($ns_array, 0, 4)) === 'osCommerce\\OM\\Core\\Site') {
                        $registry_class = implode('\\', array_slice($ns_array, 0, 5)) . '\\Registry\\' . $key;
                    }
                }
            }

            if (!isset($registry_class)) {
                $site = OSCOM::getSite();

                if (isset($site)) {
                    $registry_class = 'osCommerce\\OM\\Core\\Site\\' . $site . '\\Registry\\' . $key;
                }
            }

            if (isset($registry_class)) {
                while (!isset($value)) {
                    if (is_a($registry_class, '\\osCommerce\\OM\\Core\\RegistryAbstract', true)) {
                        $RegistryObject = new $registry_class();

                        if ($RegistryObject->hasAlias()) {
                            $registry_class = 'osCommerce\\OM\\' . $RegistryObject->getAlias();

                            continue;
                        } else {
                            $value = static::$data[$key] = $RegistryObject->getValue();
                        }
                    } else {
                        break;
                    }
                }
            }
        }

        if (!isset($value)) {
            trigger_error('OSCOM\Registry::get(): "' . $key . '" is not registered');
        }

        return $value ?? null;
    }

    public static function set(string $key, $value, bool $force = false): bool
    {
        if (static::exists($key) && ($force !== true)) {
            trigger_error('OSCOM\Registry::set(): "' . $key . '" is already registered and is not forced to be replaced');

            return false;
        }

        static::$data[$key] = $value;

        return true;
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, static::$data);
    }

    public static function remove(string $key)
    {
        unset(static::$data[$key]);
    }

    public static function addAlias(string $key, string $class): bool
    {
        if (static::aliasExists($key)) {
            trigger_error('OSCOM\Registry::addAlias(): "' . $key . '" is already registered to "' . static::$aliases[$key] . '" and cannot be replaced by "' . $class . '"');

            return false;
        }

        static::$aliases[$key] = $class;

        return true;
    }

    public static function addAliases(array $keys)
    {
        foreach ($keys as $key => $class) {
            static::addAlias($key, $class);
        }
    }

    public static function aliasExists(string $key): bool
    {
        return array_key_exists($key, static::$aliases);
    }

    public static function removeAlias(string $key)
    {
        unset(static::$aliases[$key]);
    }
}
