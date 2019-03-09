<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

use osCommerce\OM\Core\OSCOM;

abstract class ApplicationModelAbstract
{
    public static function __callStatic(string $name, array $arguments)
    {
        $class = get_called_class();

        $ns = mb_substr($class, 0, mb_strrpos($class, '\\'));

        return call_user_func_array([$ns . '\\Model\\' . $name, 'execute'], $arguments);
    }
}
