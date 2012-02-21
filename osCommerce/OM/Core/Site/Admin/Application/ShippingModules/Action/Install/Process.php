<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\ShippingModules\Action\Install;

  use osCommerce\OM\Core\ApplicationAbstract;
  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;
  use osCommerce\OM\Core\Site\Admin\Application\ShippingModules\ShippingModules;

/**
 * @since v3.0.4
 */

  class Process {
    public static function execute(ApplicationAbstract $application) {
      $data = HTML::sanitize(basename($_GET['code']));

      if ( ShippingModules::install($data) ) {
        OSCOM::redirect(OSCOM::getLink(null, null, 'Save&code=' . $_GET['code']));
      } else {
        Registry::get('MessageStack')->add(null, OSCOM::getDef('ms_error_action_not_performed'), 'error');

        OSCOM::redirect(OSCOM::getLink());
      }
    }
  }
?>
