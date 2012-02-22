<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\OrderTotalModules\Action\Uninstall;

  use osCommerce\OM\Core\ApplicationAbstract;
  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;
  use osCommerce\OM\Core\Site\Admin\Application\OrderTotalModules\OrderTotalModules;

/**
 * @since v3.0.4
 */

  class Process {
    public static function execute(ApplicationAbstract $application) {
      $data = HTML::sanitize(basename($_GET['code']));

      if ( OrderTotalModules::uninstall($data) ) {
        Registry::get('MessageStack')->add(null, OSCOM::getDef('ms_success_action_performed'), 'success');
      } else {
        Registry::get('MessageStack')->add(null, OSCOM::getDef('ms_error_action_not_performed'), 'error');
      }

      OSCOM::redirect(OSCOM::getLink());
    }
  }
?>
