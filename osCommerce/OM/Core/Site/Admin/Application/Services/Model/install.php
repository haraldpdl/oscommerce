<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\Services\Model;

  use osCommerce\OM\Core\Cache;
  use osCommerce\OM\Core\OSCOM;

/**
 * @since v3.0.2
 */

  class install {
    public static function execute($module) {
      $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Service\\' . $module . '\\Controller';

      if ( class_exists($class) ) {
        $OSCOM_SM = new $class();
        $OSCOM_SM->install();

        $sm = explode(';', MODULE_SERVICES_INSTALLED);

        if ( isset($OSCOM_SM->depends) ) {
          if ( is_string($OSCOM_SM->depends) && ( ( $key = array_search($OSCOM_SM->depends, $sm) ) !== false ) ) {
            if ( isset($sm[$key+1]) ) {
              array_splice($sm, $key+1, 0, $module);
            } else {
              $sm[] = $module;
            }
          } elseif ( is_array($OSCOM_SM->depends) ) {
            foreach ( $OSCOM_SM->depends as $depends_module ) {
              if ( ( $key = array_search($depends_module, $sm) ) !== false ) {
                if ( !isset($array_position) || ( $key > $array_position ) ) {
                  $array_position = $key;
                }
              }
            }

            if ( isset($array_position) ) {
              array_splice($sm, $array_position+1, 0, $module);
            } else {
              $sm[] = $module;
            }
          }
        } elseif ( isset($OSCOM_SM->precedes) ) {
          if ( is_string($OSCOM_SM->precedes) ) {
            if ( ( $key = array_search($OSCOM_SM->precedes, $sm) ) !== false ) {
              array_splice($sm, $key, 0, $module);
            } else {
              $sm[] = $module;
            }
          } elseif ( is_array($OSCOM_SM->precedes) ) {
            foreach ( $OSCOM_SM->precedes as $precedes_module ) {
              if ( ( $key = array_search($precedes_module, $sm) ) !== false ) {
                if ( !isset($array_position) || ( $key < $array_position ) ) {
                  $array_position = $key;
                }
              }
            }

            if ( isset($array_position) ) {
              array_splice($sm, $array_position, 0, $module);
            } else {
              $sm[] = $module;
            }
          }
        } else {
          $sm[] = $module;
        }

        $data = array('key' => 'MODULE_SERVICES_INSTALLED',
                      'value' => implode(';', $sm));

        if ( OSCOM::callDB('Admin\UpdateConfigurationParameters', $data, 'Site') ) {
          Cache::clear('configuration');

          return true;
        }
      }

      return false;
    }
  }
?>
