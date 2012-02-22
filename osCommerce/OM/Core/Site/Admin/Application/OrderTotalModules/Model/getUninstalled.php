<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\OrderTotalModules\Model;

  use osCommerce\OM\Core\DirectoryListing;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

/**
 * @since v3.0.4
 */

  class getUninstalled {
    public static function execute() {
      $OSCOM_Language = Registry::get('Language');

      $installed_modules = getInstalled::execute();
      $installed = array();

      foreach ( $installed_modules['entries'] as $module ) {
        $installed[] = $module['code'];
      }

      $modules = array();

      $DLotm = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Custom/Site/Admin/Module/OrderTotal');
      $DLotm->setIncludeFiles(false);
      $DLotm->setIncludeDirectories(true);

      foreach ( $DLotm->getFiles() as $file ) {
        $module = $file['name'];

        if ( !in_array($module, $installed) ) {
          $modules[] = $module;
        }
      }

      $DLotm = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Core/Site/Admin/Module/OrderTotal');
      $DLotm->setIncludeFiles(false);
      $DLotm->setIncludeDirectories(true);

      foreach ( $DLotm->getFiles() as $file ) {
        $module = $file['name'];

        if ( !in_array($module, $modules) && !in_array($module, $installed) ) {
          $modules[] = $module;
        }
      }

      $result = array('entries' => array());

      foreach ( $modules as $module ) {
        $class = 'osCommerce\\OM\\Core\\Site\\Admin\\Module\\OrderTotal\\' . $module . '\\Controller';

        $OSCOM_Language->injectDefinitions('modules/order_total/' . $module . '.xml');

        $OSCOM_OTM = new $class();

        $result['entries'][] = array('code' => $OSCOM_OTM->getCode(),
                                     'title' => $OSCOM_OTM->getTitle(),
                                     'sort_order' => $OSCOM_OTM->getSortOrder(),
                                     'status' => $OSCOM_OTM->isEnabled());
      }

      $result['total'] = count($result['entries']);

      return $result;
    }
  }
?>
