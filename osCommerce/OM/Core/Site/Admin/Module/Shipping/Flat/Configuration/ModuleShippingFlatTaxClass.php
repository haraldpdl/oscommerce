<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\Shipping\Flat\Configuration;

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

/**
 * @since v3.0.4
 */

  class ModuleShippingFlatTaxClass extends \osCommerce\OM\Core\Site\Admin\Module\ConfigurationAbstract {
    static protected $_sort = 300;
    static protected $_default = '0';
    static protected $_group_id = 6;

    public function initialize() { }

    public function get() {
      $OSCOM_PDO = Registry::get('PDO');
      $OSCOM_Language = Registry::get('Language');

      if ( $this->getRaw() < 1 ) {
        return OSCOM::getDef('parameter_none');
      }

      $Qclass = $OSCOM_PDO->prepare('select tax_class_title from :table_tax_class where tax_class_id = :tax_class_id');
      $Qclass->bindInt(':tax_class_id', $this->getRaw());
      $Qclass->execute();

      return $Qclass->value('tax_class_title');
    }

    public function getField() {
      $OSCOM_Language = Registry::get('Language');
      $OSCOM_PDO = Registry::get('PDO');

      $tax_class_array = array(array('id' => '0',
                                     'text' => OSCOM::getDef('parameter_none')));

      $Qclasses = $OSCOM_PDO->query('select tax_class_id, tax_class_title from :table_tax_class order by tax_class_title');
      $Qclasses->execute();

      while ( $Qclasses->fetch() ) {
        $tax_class_array[] = array('id' => $Qclasses->valueInt('tax_class_id'),
                                   'text' => $Qclasses->value('tax_class_title'));
      }

      return '<label for="cfg' . $this->_module . '">' . $this->getTitle() . '</label>' . HTML::selectMenu('configuration[' . $this->_key . ']', $tax_class_array, $this->getRaw(), 'id="cfg' . $this->_module . '"');
    }
  }
?>
