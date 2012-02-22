<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Setup\Application\Install\Model;

  use osCommerce\OM\Core\DirectoryListing;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\PDO;
  use osCommerce\OM\Core\Registry;
  use osCommerce\OM\Core\Site\Setup\Language;

  class importDB {
    public static function execute($data) {
      Registry::set('PDO', PDO::initialize($data['server'], $data['username'], $data['password'], $data['database'], $data['port'], $data['class']));

// Import SQL queries

      OSCOM::callDB('Setup\Install\ImportSQL', array('table_prefix' => $data['table_prefix']));

// Import language definitions

      OSCOM::setConfig('db_table_prefix', $data['table_prefix'], 'Admin');
      OSCOM::setConfig('db_table_prefix', $data['table_prefix'], 'Shop');
      OSCOM::setConfig('db_table_prefix', $data['table_prefix'], 'Setup');

      foreach ( Language::extractDefinitions('en_US.xml') as $def ) {
        $def['id'] = 1;

        OSCOM::callDB('Admin\InsertLanguageDefinition', $def, 'Site');
      }

      $DL_lang = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Core/Site/Shop/Languages/en_US');
      $DL_lang->setRecursive(true);
      $DL_lang->setIncludeDirectories(false);
      $DL_lang->setAddDirectoryToFilename(true);
      $DL_lang->setCheckExtension('xml');

      foreach ( $DL_lang->getFiles() as $files ) {
        foreach ( Language::extractDefinitions('en_US/' . $files['name']) as $def ) {
          $def['id'] = 1;

          OSCOM::callDB('Admin\InsertLanguageDefinition', $def, 'Site');
        }
      }

// Import Configuration modules

      $modules = array();

      $DL = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Core/Site/Admin/Module/Configuration');
      $DL->setIncludeDirectories(false);
      $DL->setCheckExtension('php');

      foreach ( $DL->getFiles() as $file ) {
        $modules[] = substr($file['name'], 0, strpos($file['name'], '.'));
      }

      $DL = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Custom/Site/Admin/Module/Configuration');
      $DL->setIncludeDirectories(false);
      $DL->setCheckExtension('php');

      foreach ( $DL->getFiles() as $file ) {
        $module = substr($file['name'], 0, strpos($file['name'], '.'));

        if ( !in_array($module, $modules) ) {
          $modules[] = $module;
        }
      }

      foreach ( $modules as $cfg_module ) {
        $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Configuration\\' . $cfg_module;

        $module = new $class();
        $module->install();
      }

// Import Service modules

      $services = array('OutputCompression',
                        'Session',
                        'Language',
                        'Debug',
                        'Currencies',
                        'Core',
                        'SimpleCounter',
                        'CategoryPath',
                        'Breadcrumb',
                        'WhosOnline',
// HPDL                   'banner',
                        'Specials',
                        'Reviews',
                        'RecentlyVisited');

      $installed = array();

      foreach ( $services as $service ) {
        $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Service\\' . $service . '\\Controller';
        $module = new $class();
        $module->install();

        if ( isset($module->depends) ) {
          if ( is_string($module->depends) && (($key = array_search($module->depends, $installed)) !== false) ) {
            if ( isset($installed[$key+1]) ) {
              array_splice($installed, $key+1, 0, $service);
            } else {
              $installed[] = $service;
            }
          } elseif ( is_array($module->depends) ) {
            foreach ( $module->depends as $depends_module ) {
              if ( ($key = array_search($depends_module, $installed)) !== false ) {
                if ( !isset($array_position) || ($key > $array_position) ) {
                  $array_position = $key;
                }
              }
            }

            if ( isset($array_position) ) {
              array_splice($installed, $array_position+1, 0, $service);
            } else {
              $installed[] = $service;
            }
          }
        } elseif ( isset($module->precedes) ) {
          if ( is_string($module->precedes) ) {
            if ( ($key = array_search($module->precedes, $installed)) !== false ) {
              array_splice($installed, $key, 0, $service);
            } else {
              $installed[] = $service;
            }
          } elseif ( is_array($module->precedes) ) {
            foreach ( $module->precedes as $precedes_module ) {
              if ( ($key = array_search($precedes_module, $installed)) !== false ) {
                if ( !isset($array_position) || ($key < $array_position) ) {
                  $array_position = $key;
                }
              }
            }

            if ( isset($array_position) ) {
              array_splice($installed, $array_position, 0, $service);
            } else {
              $installed[] = $service;
            }
          }
        } else {
          $installed[] = $service;
        }

        unset($array_position);
      }

      $cfg_data = array('key' => 'MODULE_SERVICES_INSTALLED',
                        'value' => implode(';', $installed),
                        'group_id' => '6');

      OSCOM::callDB('Admin\InsertConfigurationParameters', $cfg_data, 'Site');

// Import Payment modules

      define('DEFAULT_ORDERS_STATUS_ID', 1);

      $module = new \osCommerce\OM\Core\Site\Admin\Module\Payment\COD\Controller();
      $module->install();

      $pm_data = array('key' => 'MODULE_PAYMENT_COD_STATUS',
                       'value' => '1');

      OSCOM::callDB('Admin\UpdateConfigurationParameters', $pm_data, 'Site');

// Import Shipping modules

      $module = new \osCommerce\OM\Core\Site\Admin\Module\Shipping\Flat\Controller();
      $module->install();

      $sm_data = array('key' => 'MODULE_SHIPPING_FLAT_STATUS',
                       'value' => 'True');

      OSCOM::callDB('Admin\UpdateConfigurationParameters', $sm_data, 'Site');

// Import Order Total modules

      $module = new \osCommerce\OM\Core\Site\Admin\Module\OrderTotal\SubTotal\Controller();
      $module->install();

      $module = new \osCommerce\OM\Core\Site\Admin\Module\OrderTotal\Shipping\Controller();
      $module->install();

      $module = new \osCommerce\OM\Core\Site\Admin\Module\OrderTotal\Tax\Controller();
      $module->install();

      $module = new \osCommerce\OM\Core\Site\Admin\Module\OrderTotal\Total\Controller();
      $module->install();

// Import Foreign Keys

      OSCOM::callDB('Setup\Install\ImportFK', array('table_prefix' => $data['table_prefix']));
    }
  }
?>
