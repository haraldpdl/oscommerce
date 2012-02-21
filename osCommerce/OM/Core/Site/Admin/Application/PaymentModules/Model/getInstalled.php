<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\PaymentModules\Model;

  use osCommerce\OM\Core\Registry;
  use osCommerce\OM\Core\OSCOM;

  class getInstalled {
    public static function execute() {
      $OSCOM_Language = Registry::get('Language');

      $result = OSCOM::callDB('Admin\PaymentModules\GetAll');

      foreach ( $result['entries'] as &$module ) {
        $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Payment\\' . $module['code'] . '\\Controller';

        $OSCOM_Language->injectDefinitions('modules/payment/' . $module['code'] . '.xml');

        $OSCOM_PM = new $class();

        $module['code'] = $OSCOM_PM->getCode();
        $module['title'] = $OSCOM_PM->getTitle();
        $module['sort_order'] = $OSCOM_PM->getSortOrder();
        $module['status'] = $OSCOM_PM->isInstalled() && $OSCOM_PM->isEnabled();
      }

      return $result;
    }
  }
?>
