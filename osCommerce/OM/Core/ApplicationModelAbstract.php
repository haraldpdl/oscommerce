<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

abstract class ApplicationModelAbstract
{
    public static function __callStatic(string $name, array $arguments)
    {
        $class = new \ReflectionClass(get_called_class());

        $callable = [
            $class->getNamespaceName() . '\\Model\\' . $name,
            'execute'
        ];

        if (is_callable($callable)) {
            return call_user_func_array($callable, $arguments);
        }
    }
}
