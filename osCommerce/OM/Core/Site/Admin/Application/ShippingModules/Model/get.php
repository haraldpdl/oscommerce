<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\ShippingModules\Model;

  use osCommerce\OM\Core\Registry;

/**
 * @since v3.0.4
 */

  class get {
    public static function execute($code) {
      $OSCOM_Language = Registry::get('Language');

      $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Shipping\\' . $code . '\\Controller';

      $OSCOM_Language->injectDefinitions('modules/shipping/' . $code . '.xml');

      $OSCOM_SM = new $class();

      $result = array('code' => $OSCOM_SM->getCode(),
                      'title' => $OSCOM_SM->getTitle(),
                      'sort_order' => $OSCOM_SM->getSortOrder(),
                      'status' => $OSCOM_SM->isEnabled(),
                      'keys' => $OSCOM_SM->getKeys());

      return $result;
    }
  }
?>
