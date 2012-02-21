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

  class ModulePaymentCodZone extends \osCommerce\OM\Core\Site\Admin\Module\ConfigurationAbstract {
    static protected $_sort = 200;
    static protected $_default = '0';
    static protected $_group_id = 6;

    public function initialize() { }

    public function get() {
      $OSCOM_PDO = Registry::get('PDO');
      $OSCOM_Language = Registry::get('Language');

      if ( $this->getRaw() == '0' ) {
        return OSCOM::getDef('parameter_none');
      }

      $Qclass = $OSCOM_PDO->prepare('select geo_zone_name from :table_geo_zones where geo_zone_id = :geo_zone_id');
      $Qclass->bindInt(':geo_zone_id', $this->getRaw());
      $Qclass->execute();

      return $Qclass->value('geo_zone_name');
    }

    public function getField() {
      $OSCOM_PDO = Registry::get('PDO');

      $zone_class_array = array(array('id' => '0',
                                      'text' => OSCOM::getDef('parameter_none')));

      $Qzones = $OSCOM_PDO->query('select geo_zone_id, geo_zone_name from :table_geo_zones order by geo_zone_name');
      $Qzones->execute();

      while ( $Qzones->fetch() ) {
        $zone_class_array[] = array('id' => $Qzones->valueInt('geo_zone_id'),
                                    'text' => $Qzones->value('geo_zone_name'));
      }

      return '<label for="cfg' . $this->_module . '">' . $this->getTitle() . '</label>' . HTML::selectMenu('configuration[' . $this->_key . ']', $zone_class_array, $this->getRaw(), 'id="cfg' . $this->_module . '"');
    }
  }
?>
