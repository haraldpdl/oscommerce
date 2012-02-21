<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\ShippingModules\Model;

  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

/**
 * @since v3.0.4
 */

  class getInstalled {
    public static function execute() {
      $OSCOM_Language = Registry::get('Language');

      $result = OSCOM::callDB('Admin\ShippingModules\GetAll');

      foreach ( $result['entries'] as &$module ) {
        $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Shipping\\' . $module['code'] . '\\Controller';

        $OSCOM_Language->injectDefinitions('modules/shipping/' . $module['code'] . '.xml');

        $OSCOM_SM = new $class();

        $module['code'] = $OSCOM_SM->getCode();
        $module['title'] = $OSCOM_SM->getTitle();
        $module['sort_order'] = $OSCOM_SM->getSortOrder();
        $module['status'] = $OSCOM_SM->isInstalled() && $OSCOM_SM->isEnabled();
      }

      return $result;
    }
  }
?>
