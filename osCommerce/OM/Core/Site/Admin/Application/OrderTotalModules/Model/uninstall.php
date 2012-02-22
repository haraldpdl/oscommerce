<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\OrderTotalModules\Model;

  use osCommerce\OM\Core\Cache;
  use osCommerce\OM\Core\Registry;

/**
 * @since v3.0.4
 */

  class uninstall {
    public static function execute($module) {
      $OSCOM_Language = Registry::get('Language');

      $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\OrderTotal\\' . $module . '\\Controller';

      if ( class_exists($class) ) {
        $OSCOM_Language->injectDefinitions('modules/order_total/' . $module . '.xml');

        $OSCOM_OTM = new $class();
        $OSCOM_OTM->remove();

        Cache::clear('modules-order_total');
        Cache::clear('configuration');

        return true;
      }

      return false;
    }
  }
?>
