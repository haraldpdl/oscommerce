<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Template\Tag;

use osCommerce\OM\Core\Registry;

class raw extends \osCommerce\OM\Core\Template\TagAbstract
{
    protected static $_parse_result = false;

    public static function execute($string)
    {
        $args = func_get_args();

        $OSCOM_Template = Registry::get('Template');

        $result = '';

        if (strpos($string, ' ') === false) {
            if ($OSCOM_Template->valueExists($string)) {
                $result = $OSCOM_Template->getValue($string);
            }
        } else {
            [$array, $key] = explode(' ', $string, 2);

            if ($OSCOM_Template->valueExists($array)) {
                $value = $OSCOM_Template->getValue($array);

                if (is_array($value)) {
                    $pass = true;

                    foreach (explode(' ', $key) as $k) {
                        if (isset($value[$k])) {
                            $value = $value[$k];
                        } else {
                            $pass = false;
                            break;
                        }
                    }

                    if ($pass === true) {
                        $result = $value;
                    }
                }
            }
        }

        if (isset($args[1]) && !empty($args[1])) {
            $result = call_user_func(trim($args[1]), $result);
        }

        return $result;
    }
}
