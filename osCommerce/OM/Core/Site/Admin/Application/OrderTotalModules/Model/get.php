<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\OrderTotalModules\Model;

  use osCommerce\OM\Core\Registry;

/**
 * @since v3.0.4
 */

  class get {
    public static function execute($code) {
      $OSCOM_Language = Registry::get('Language');

      $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\OrderTotal\\' . $code . '\\Controller';

      $OSCOM_Language->injectDefinitions('modules/order_total/' . $code . '.xml');

      $OSCOM_OTM = new $class();

      $result = array('code' => $OSCOM_OTM->getCode(),
                      'title' => $OSCOM_OTM->getTitle(),
                      'sort_order' => $OSCOM_OTM->getSortOrder(),
                      'status' => $OSCOM_OTM->isEnabled(),
                      'keys' => $OSCOM_OTM->getKeys());

      return $result;
    }
  }
?>
