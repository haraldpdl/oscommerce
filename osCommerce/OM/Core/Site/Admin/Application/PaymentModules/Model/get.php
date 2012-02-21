<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\PaymentModules\Model;

  use osCommerce\OM\Core\Registry;

  class get {
    public static function execute($code) {
      $OSCOM_Language = Registry::get('Language');

      $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Payment\\' . $code . '\\Controller';

      $OSCOM_Language->injectDefinitions('modules/payment/' . $code . '.xml');

      $OSCOM_PM = new $class();

      $result = array('code' => $OSCOM_PM->getCode(),
                      'title' => $OSCOM_PM->getTitle(),
                      'sort_order' => $OSCOM_PM->getSortOrder(),
                      'status' => $OSCOM_PM->isEnabled(),
                      'keys' => $OSCOM_PM->getKeys());

      return $result;
    }
  }
?>
