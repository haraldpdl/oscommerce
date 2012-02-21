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

  class Save {
    public static function execute($data) {
      $OSCOM_PDO = Registry::get('PDO');

      $error = false;

      $OSCOM_PDO->beginTransaction();

      foreach ( $data['configuration'] as $key => $value ) {
        $Qupdate = $OSCOM_PDO->prepare('update :table_configuration set configuration_value = :configuration_value where configuration_key = :configuration_key');
        $Qupdate->bindValue(':configuration_value', is_array($data['configuration'][$key]) ? implode(',', $data['configuration'][$key]) : $value);
        $Qupdate->bindValue(':configuration_key', $key);
        $Qupdate->execute();

        if ( $Qupdate->isError() ) {
          $error = true;
          break;
        }
      }

      if ( $error === false ) {
        $OSCOM_PDO->commit();

        return true;
      }

      $OSCOM_PDO->rollBack();

      return false;
    }
  }
?>
