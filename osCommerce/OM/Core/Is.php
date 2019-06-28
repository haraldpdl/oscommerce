<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

class Is
{
    public static function __callStatic(string $name, array $arguments): bool
    {
        $class = __NAMESPACE__ . '\\Is\\' . $name;

        try {
            if (!class_exists($class)) {
                throw new \Exception('OSCOM\Is module class does not exist: ' . $class);
            }

            if (!is_subclass_of($class, 'osCommerce\\OM\\Core\\IsInterface')) {
                throw new \Exception('OSCOM\Is module class does not implement osCommerce\OM\Core\IsInterface: ' . $class);
            }

            $callable = [
                $class,
                'execute'
            ];

            if (is_callable($callable)) {
                return call_user_func_array($callable, $arguments);
            }
        } catch (\Exception $e) {
            trigger_error($e->getMessage());
        }

        return false;
    }
}
