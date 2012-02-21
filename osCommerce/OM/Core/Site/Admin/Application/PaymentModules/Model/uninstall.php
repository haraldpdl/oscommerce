<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\PaymentModules\Model;

  use osCommerce\OM\Core\Cache;
  use osCommerce\OM\Core\Registry;

  class uninstall {
    public static function execute($module) {
      $OSCOM_Language = Registry::get('Language');

      $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Payment\\' . $module . '\\Controller';

      if ( class_exists($class) ) {
        $OSCOM_Language->injectDefinitions('modules/payment/' . $module . '.xml');

        $OSCOM_PM = new $class();
        $OSCOM_PM->remove();

        Cache::clear('modules-payment');
        Cache::clear('configuration');

        return true;
      }

      return false;
    }
  }
?>
