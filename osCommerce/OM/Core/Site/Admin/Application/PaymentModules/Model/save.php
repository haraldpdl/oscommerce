<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\PaymentModules\Model;

  use osCommerce\OM\Core\Cache;
  use osCommerce\OM\Core\OSCOM;

  class save {
    public static function execute($data) {
      $cfg = array();

      foreach ( $data['configuration'] as $k => $v ) {
        $cfg[] = array('key' => $k,
                       'value' => $v);
      }

      if ( OSCOM::callDB('Admin\UpdateConfigurationParameters', $cfg, 'Site') ) {
        Cache::clear('configuration');

        return true;
      }

      return false;
    }
  }
?>
