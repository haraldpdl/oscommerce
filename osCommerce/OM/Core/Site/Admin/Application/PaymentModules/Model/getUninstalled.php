<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\PaymentModules\Model;

  use osCommerce\OM\Core\DirectoryListing;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;
  use osCommerce\OM\Core\Site\Admin\Application\PaymentModules\PaymentModules;

  class getUninstalled {
    public static function execute() {
      $OSCOM_Language = Registry::get('Language');

      $installed_modules = PaymentModules::getInstalled();
      $installed = array();

      foreach ( $installed_modules['entries'] as $module ) {
        $installed[] = $module['code'];
      }

      $modules = array();

      $DLsm = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Custom/Site/Admin/Module/Payment');
      $DLsm->setIncludeFiles(false);
      $DLsm->setIncludeDirectories(true);

      foreach ( $DLsm->getFiles() as $file ) {
        $module = $file['name'];

        if ( !in_array($module, $installed) ) {
          $modules[] = $module;
        }
      }

      $DLsm = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Core/Site/Admin/Module/Payment');
      $DLsm->setIncludeFiles(false);
      $DLsm->setIncludeDirectories(true);

      foreach ( $DLsm->getFiles() as $file ) {
        $module = $file['name'];

        if ( !in_array($module, $modules) && !in_array($module, $installed) ) {
          $modules[] = $module;
        }
      }

      $result = array('entries' => array());

      foreach ( $modules as $module ) {
        $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\Payment\\' . $module . '\\Controller';

        $OSCOM_Language->injectDefinitions('modules/payment/' . $module . '.xml');

        $OSCOM_PM = new $class();

        $result['entries'][] = array('code' => $OSCOM_PM->getCode(),
                                     'title' => $OSCOM_PM->getTitle(),
                                     'sort_order' => $OSCOM_PM->getSortOrder(),
                                     'status' => $OSCOM_PM->isEnabled());
      }

      $result['total'] = count($result['entries']);

      return $result;
    }
  }
?>
