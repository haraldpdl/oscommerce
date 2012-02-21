<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\ShippingModules\Action;

  use osCommerce\OM\Core\ApplicationAbstract;

/**
 * @since v3.0.4
 */

  class Save {
    public static function execute(ApplicationAbstract $application) {
      $application->setPageContent('edit.php');
    }
  }
?>
