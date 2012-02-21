<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2011 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module;

  use osCommerce\OM\Core\Cache;
  use osCommerce\OM\Core\DirectoryListing;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

  abstract class PaymentAbstract {
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
      $module_class = array_slice(explode('\\', get_called_class()), -2, 1);
      $this->_code = $module_class[0];

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
      $keys = $this->getKeys();

      return ( is_array($keys) && !empty($keys) );
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @return array
 */

    public function getKeys() {
      $modules = array();
      $result = array();

      foreach ( $this->getConfigurationModules() as $cfg_module ) {
        $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Payment\\' . $this->_code . '\\Configuration\\' . $cfg_module;

        $modules[] = array('key' => strtoupper(implode('_', preg_split('/(?=[A-Z])/', $cfg_module, null, PREG_SPLIT_NO_EMPTY))),
                           'sort' => call_user_func(array($class, 'getSort')));
      }

      usort($modules, function($a, $b) {
        return strnatcmp($a['sort'], $b['sort']);
      });

      foreach ( $modules as $m ) {
        $result[] = $m['key'];
      }

      return $result;
    }

/**
 * Installs the module
 */

    public function install() {
      $OSCOM_Language = Registry::get('Language');

      $data = array('title' => $this->_title,
                    'code' => $this->_code,
                    'author_name' => $this->_author_name,
                    'author_www' => $this->_author_www,
                    'group' => 'Payment');

      OSCOM::callDB('Admin\InsertModule', $data, 'Site');

      foreach ( $this->getConfigurationModules() as $cfg_module ) {
        $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Payment\\' . $this->_code . '\\Configuration\\' . $cfg_module;

        $module = new $class();
        $module->install();
      }

      foreach ( $OSCOM_Language->getAll() as $key => $value ) {
        if ( file_exists(OSCOM::BASE_DIRECTORY . 'Custom/Site/Shop/Languages/' . $key . '/modules/payment/' . $this->_code . '.xml') || file_exists(OSCOM::BASE_DIRECTORY . 'Core/Site/Shop/Languages/' . $key . '/modules/payment/' . $this->_code . '.xml') ) {
          foreach ( $OSCOM_Language->extractDefinitions($key . '/modules/payment/' . $this->_code . '.xml') as $def ) {
            $def['id'] = $value['id'];

            OSCOM::callDB('Admin\InsertLanguageDefinition', $def, 'Site');
          }
        }
      }

      Cache::clear('languages');
    }

/**
 * Uninstalls the module
 */

    public function remove() {
      $OSCOM_Language = Registry::get('Language');

      $data = array('code' => $this->_code,
                    'group' => 'Payment');

      OSCOM::callDB('Admin\DeleteModule', $data, 'Site');

      foreach ( $this->getConfigurationModules() as $cfg_module ) {
        $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Payment\\' . $this->_code . '\\Configuration\\' . $cfg_module;

        $module = new $class();
        $module->uninstall();
      }

      if ( file_exists(OSCOM::BASE_DIRECTORY . 'Custom/Site/Shop/Languages/' . $OSCOM_Language->getCode() . '/modules/payment/' . $this->_code . '.xml') || file_exists(OSCOM::BASE_DIRECTORY . 'Core/Site/Shop/Languages/' . $OSCOM_Language->getCode() . '/modules/payment/' . $this->_code . '.xml') ) {
        foreach ( $OSCOM_Language->extractDefinitions($OSCOM_Language->getCode() . '/modules/payment/' . $this->_code . '.xml') as $def ) {
          OSCOM::callDB('Admin\DeleteLanguageDefinitions', $def, 'Site');
        }

        Cache::clear('languages');
      }
    }

    public function getConfigurationModules() {
      $modules = array();

      $DL = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Core/Site/Admin/Module/Payment/' . $this->_code . '/Configuration');
      $DL->setIncludeDirectories(false);
      $DL->setCheckExtension('php');

      foreach ( $DL->getFiles() as $file ) {
        $modules[] = substr($file['name'], 0, strpos($file['name'], '.'));
      }

      $DL = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Custom/Site/Admin/Module/Payment/' . $this->_code . '/Configuration');
      $DL->setIncludeDirectories(false);
      $DL->setCheckExtension('php');

      foreach ( $DL->getFiles() as $file ) {
        $module = substr($file['name'], 0, strpos($file['name'], '.'));

        if ( !in_array($module, $modules) ) {
          $modules[] = $module;
        }
      }

      return $modules;
    }
  }
?>
