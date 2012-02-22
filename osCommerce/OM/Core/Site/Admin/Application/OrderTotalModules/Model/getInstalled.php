<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\OrderTotalModules\Model;

  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

/**
 * @since v3.0.4
 */

  class getInstalled {
    public static function execute() {
      $OSCOM_Language = Registry::get('Language');

      $result = OSCOM::callDB('Admin\OrderTotalModules\GetAll');

      foreach ( $result['entries'] as &$module ) {
        $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\OrderTotal\\' . $module['code'] . '\\Controller';

        $OSCOM_Language->injectDefinitions('modules/order_total/' . $module['code'] . '.xml');

        $OSCOM_OTM = new $class();

        $module['code'] = $OSCOM_OTM->getCode();
        $module['title'] = $OSCOM_OTM->getTitle();
        $module['sort_order'] = $OSCOM_OTM->getSortOrder();
        $module['status'] = $OSCOM_OTM->isInstalled() && $OSCOM_OTM->isEnabled();
      }

      return $result;
    }
  }
?>
