<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Is;

class Integer implements \osCommerce\OM\Core\IsInterface
{
    public static function execute($value, int $min = null, int $max = null): bool
    {
        $options = [];

        if (isset($min)) {
            $options['options']['min_range'] = $min;
        }

        if (isset($max)) {
            $options['options']['max_range'] = $max;
        }

        return filter_var($value, \FILTER_VALIDATE_INT, $options) !== false;
    }
}
