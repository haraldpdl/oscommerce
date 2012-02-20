<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2011 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin;

  use osCommerce\OM\Core\Registry;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Cache;

  abstract class PaymentModuleAbstract {
    protected $_code;
    protected $_title;
    protected $_description;
    protected $_author_name;
    protected $_author_www;
    protected $_status;
    protected $_sort_order = 0;

    abstract protected function initialize();
    abstract public function isInstalled();

    public function __construct() {
      $module_class = explode('\\', get_called_class());
      $this->_code = end($module_class);

      $this->initialize();
    }

    public function isEnabled() {
      return $this->_status;
    }

    public function getCode() {
      return $this->_code;
    }

    public function getTitle() {
      return $this->_title;
    }

    public function getSortOrder() {
      return $this->_sort_order;
    }

    public function hasKeys() {
      return (count($this->getKeys()) > 0);
    }

    public function getKeys() {
      return array();
    }

    public function install() {
      $OSCOM_Language = Registry::get('Language');

      $data = array('title' => $this->_title,
                    'code' => $this->_code,
                    'author_name' => $this->_author_name,
                    'author_www' => $this->_author_www,
                    'group' => 'Payment');

      OSCOM::callDB('Admin\InsertModule', $data, 'Site');

      foreach ( $OSCOM_Language->getAll() as $key => $value ) {
        if ( file_exists(OSCOM::BASE_DIRECTORY . 'Core/Site/Shop/Languages/' . $key . '/modules/payment/' . $this->_code . '.xml') ) {
          foreach ( $OSCOM_Language->extractDefinitions($key . '/modules/payment/' . $this->_code . '.xml') as $def ) {
            $def['id'] = $value['id'];

            OSCOM::callDB('Admin\InsertLanguageDefinition', $def, 'Site');
          }
        }
      }

      Cache::clear('languages');
    }

    public function remove() {
      $OSCOM_Language = Registry::get('Language');

      $data = array('code' => $this->_code,
                    'group' => 'Payment');

      OSCOM::callDB('Admin\DeleteModule', $data, 'Site');

      if ( $this->hasKeys() ) {
        OSCOM::callDB('Admin\DeleteConfigurationParameters', $this->getKeys(), 'Site');

        Cache::clear('configuration');
      }

      if ( file_exists(OSCOM::BASE_DIRECTORY . 'Core/Site/Shop/Languages/' . $OSCOM_Language->getCode() . '/modules/payment/' . $this->_code . '.xml') ) {
        foreach ( $OSCOM_Language->extractDefinitions($OSCOM_Language->getCode() . '/modules/payment/' . $this->_code . '.xml') as $def ) {
          OSCOM::callDB('Admin\DeleteLanguageDefinitions', $def, 'Site');
        }

        Cache::clear('languages');
      }
    }
  }
?>
