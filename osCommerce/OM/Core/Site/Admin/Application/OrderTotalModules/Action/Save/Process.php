<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\OrderTotalModules\Action\Save;

  use osCommerce\OM\Core\ApplicationAbstract;
  use osCommerce\OM\Core\Site\Admin\Application\OrderTotalModules\OrderTotalModules;
  use osCommerce\OM\Core\Registry;
  use osCommerce\OM\Core\OSCOM;

/**
 * @since v3.0.4
 */

  class Process {
    public static function execute(ApplicationAbstract $application) {
      $data = array('configuration' => $_POST['configuration']);

      if ( OrderTotalModules::save($data) ) {
        Registry::get('MessageStack')->add(null, OSCOM::getDef('ms_success_action_performed'), 'success');
      } else {
        Registry::get('MessageStack')->add(null, OSCOM::getDef('ms_error_action_not_performed'), 'error');
      }

      OSCOM::redirect(OSCOM::getLink());
    }
  }
?>
