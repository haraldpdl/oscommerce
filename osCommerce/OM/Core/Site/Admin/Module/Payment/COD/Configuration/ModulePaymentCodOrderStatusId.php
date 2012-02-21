<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\Payment\COD\Configuration;

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

/**
 * @since v3.0.4
 */

  class ModulePaymentCodOrderStatusId extends \osCommerce\OM\Core\Site\Admin\Module\ConfigurationAbstract {
    static protected $_sort = 300;
    static protected $_default = '0';
    static protected $_group_id = 6;

    public function initialize() { }

    public function get() {
      $OSCOM_PDO = Registry::get('PDO');
      $OSCOM_Language = Registry::get('Language');

      if ( $this->getRaw() < 1 ) {
        return OSCOM::getDef('default_entry');
      }

      $Qstatus = $OSCOM_PDO->prepare('select orders_status_name from :table_orders_status where orders_status_id = :orders_status_id and language_id = :language_id');
      $Qstatus->bindInt(':orders_status_id', $this->getRaw());
      $Qstatus->bindInt(':language_id', $OSCOM_Language->getID());
      $Qstatus->execute();

      return $Qstatus->value('orders_status_name');
    }

    public function getField() {
      $OSCOM_PDO = Registry::get('PDO');
      $OSCOM_Language = Registry::get('Language');

      $status_array = array(array('id' => '0',
                                  'text' => OSCOM::getDef('default_entry')));

      $Qstatus = $OSCOM_PDO->prepare('select orders_status_id, orders_status_name from :table_orders_status where language_id = :language_id order by orders_status_name');
      $Qstatus->bindInt(':language_id', $OSCOM_Language->getID());
      $Qstatus->execute();

      while ( $Qstatus->fetch() ) {
        $status_array[] = array('id' => $Qstatus->valueInt('orders_status_id'),
                                'text' => $Qstatus->value('orders_status_name'));
      }

      return '<label for="cfg' . $this->_module . '">' . $this->getTitle() . '</label>' . HTML::selectMenu('configuration[' . $this->_key . ']', $status_array, $this->getRaw(), 'id="cfg' . $this->_module . '"');
    }
  }
?>
