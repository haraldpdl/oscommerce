<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\ShippingModules\Model;

  use osCommerce\OM\Core\Cache;
  use osCommerce\OM\Core\Registry;

/**
 * @since v3.0.4
 */

  class uninstall {
    public static function execute($module) {
      $OSCOM_Language = Registry::get('Language');

      $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Shipping\\' . $module . '\\Controller';

      if ( class_exists($class) ) {
        $OSCOM_Language->injectDefinitions('modules/shipping/' . $module . '.xml');

        $OSCOM_SM = new $class();
        $OSCOM_SM->remove();

        Cache::clear('modules-shipping');
        Cache::clear('configuration');

        return true;
      }

      return false;
    }
  }
?>
