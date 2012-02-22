<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2011 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\Configuration\Model;

  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Cache;

  class saveEntry {
    public static function execute($data) {
      if ( OSCOM::callDB('Admin\UpdateConfigurationParameters', $data, 'Site') ) {
        Cache::clear('configuration');

        return true;
      }

      return false;
    }
  }
?>
