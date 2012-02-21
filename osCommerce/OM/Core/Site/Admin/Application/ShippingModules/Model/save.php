<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\ShippingModules\Model;

  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Cache;

/**
 * @since v3.0.4
 */

  class save {
    public static function execute($data) {
      if ( OSCOM::callDB('Admin\ShippingModules\Save', $data) ) {
        Cache::clear('configuration');

        return true;
      }

      return false;
    }
  }
?>
