<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Template\Tag;

use osCommerce\OM\Core\{
    HTML,
    OSCOM,
    Registry
};

class loop extends \osCommerce\OM\Core\Template\TagAbstract
{
    protected static $_parse_result = false;

    public static function execute($string)
    {
        $args = func_get_args();

        $OSCOM_Template = Registry::get('Template');

        $key = trim($args[1]);

        if (!$OSCOM_Template->valueExists($key)) {
            if (class_exists('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Module\\Template\\Value\\' . $key . '\\Controller') && is_subclass_of('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'osCommerce\\OM\\Core\\Template\\ValueAbstract')) {
                call_user_func(['osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'initialize']);
            } elseif (class_exists('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Module\\Template\\Value\\' . $key . '\\Controller') && is_subclass_of('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'osCommerce\\OM\\Core\\Template\\ValueAbstract')) {
                call_user_func(['osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'initialize']);
            }
        }

        $data = $OSCOM_Template->getValue($key);

        if (isset($args[2])) {
            $data = $data[$args[2]];
        }

        $result = '';

        if (!empty($data)) {
            foreach ($data as $d) {
                $result .= preg_replace_callback('/([#|%])([a-zA-Z0-9_-]+)\1/', function ($matches) use (&$d) {
                    $value = $d[$matches[2]] ?? '';

                    if (substr($matches[0], 0, 1) == '#') {
                        $value = HTML::outputProtected($value);
                    }

                    return $value;
                }, $string);
            }
        }

        return $result;
    }
}
