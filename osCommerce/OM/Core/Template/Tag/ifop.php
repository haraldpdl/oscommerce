<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Template\Tag;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

class ifop extends \osCommerce\OM\Core\Template\TagAbstract
{
    protected static $_parse_result = false;

    public static function execute($string)
    {
        $args = func_get_args();

        $OSCOM_Template = Registry::get('Template');

        $key = trim($args[1]);

        list($key, $entry) = explode(' ', $key, 2);

        preg_match('/\"(.*)\"/', $entry, $matches);

        $check_against = $matches[1];

        $entry = trim(mb_substr($entry, 0, mb_strpos($entry, '"')));

        if (mb_strpos($entry, ' ') !== false) {
            list($entry, $op) = explode(' ', $entry, 2);
        } else {
            $op = $entry;
            unset($entry);
        }

        if (!$OSCOM_Template->valueExists($key)) {
            if (class_exists('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Module\\Template\\Value\\' . $key . '\\Controller') && is_subclass_of('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'osCommerce\\OM\\Core\\Template\\ValueAbstract')) {
                call_user_func(array('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'initialize'));
            } elseif (class_exists('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Module\\Template\\Value\\' . $key . '\\Controller') && is_subclass_of('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'osCommerce\\OM\\Core\\Template\\ValueAbstract')) {
                call_user_func(array('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'initialize'));
            }
        }

        $value = null;

        $has_else = mb_strpos($string, '{else}');

        $result = '';

        if ($OSCOM_Template->valueExists($key)) {
            $value = $OSCOM_Template->getValue($key);

            if (isset($entry)) {
                if (is_array($value) && array_key_exists($entry, $value)) {
                    $value = $value[$entry];
                }
            }
        }

        $pass = false;

        switch ($op) {
            case '==':
                if ($value == $check_against) {
                    $pass = true;
                }

                break;

            case '!=':
                if ($value != $check_against) {
                    $pass = true;
                }

                break;

            case '<':
                if ($value < $check_against) {
                    $pass = true;
                }

                break;

            case '<=':
                if ($value <= $check_against) {
                    $pass = true;
                }

                break;

            case '>':
                if ($value > $check_against) {
                    $pass = true;
                }

                break;

            case '>=':
                if ($value >= $check_against) {
                    $pass = true;
                }

                break;
        }

        if ($has_else !== false) {
            if ($pass === true) {
                $result = substr($string, 0, $has_else);
            } else {
                $result = substr($string, $has_else + 6); // strlen('{else}')==6
            }
        } elseif ($pass === true) {
            $result = $string;
        }

        return $result;
    }
}
