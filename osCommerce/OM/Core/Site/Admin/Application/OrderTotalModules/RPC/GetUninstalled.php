<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\OrderTotalModules\RPC;

  use osCommerce\OM\Core\Site\Admin\Application\OrderTotalModules\OrderTotalModules;
  use osCommerce\OM\Core\Site\RPC\Controller as RPC;

/**
 * @since v3.0.4
 */

  class GetUninstalled {
    public static function execute() {
      if ( !isset($_GET['search']) ) {
        $_GET['search'] = '';
      }

      if ( !empty($_GET['search']) ) {
        $result = OrderTotalModules::findUninstalled($_GET['search']);
      } else {
        $result = OrderTotalModules::getUninstalled();
      }

      $result['rpcStatus'] = RPC::STATUS_SUCCESS;

      echo json_encode($result);
    }
  }
?>
