<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\OrderTotal\Shipping;

  use osCommerce\OM\Core\OSCOM;

/**
 * @since v3.0.4
 */

  class Controller extends \osCommerce\OM\Core\Site\Admin\Module\OrderTotalAbstract {
    protected $_author_name = 'osCommerce';
    protected $_author_www = 'http://www.oscommerce.com';

    protected function initialize() {
      $this->_title = OSCOM::getDef('order_total_shipping_title');
      $this->_description = OSCOM::getDef('order_total_shipping_description');
      $this->_status = (defined('MODULE_ORDER_TOTAL_SHIPPING_STATUS') && (MODULE_ORDER_TOTAL_SHIPPING_STATUS == 'true') ? true : false);
      $this->_sort_order = (defined('MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER') ? MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER : 0);
    }

    public function isInstalled() {
      return defined('MODULE_ORDER_TOTAL_SHIPPING_STATUS');
    }
  }
?>
