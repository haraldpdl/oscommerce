<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\ShippingModules\SQL\ANSI;

  use osCommerce\OM\Core\Registry;

/**
 * @since v3.0.4
 */

  class GetAll {
    public static function execute() {
      $OSCOM_PDO = Registry::get('PDO');

      $result = array();

      $Qsm = $OSCOM_PDO->prepare('select code from :table_modules where modules_group = :modules_group order by code');
      $Qsm->bindValue(':modules_group', 'Shipping');
      $Qsm->execute();

      $result['entries'] = $Qsm->fetchAll();

      $result['total'] = count($result['entries']);

      return $result;
    }
  }
?>
