<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\Shipping\Flat;

  use osCommerce\OM\Core\OSCOM;

/**
 * @since v3.0.4
 */

  class Controller extends \osCommerce\OM\Core\Site\Admin\Module\ShippingAbstract {
    protected $_title;
    protected $_description;
    protected $_author_name = 'osCommerce';
    protected $_author_www = 'http://www.oscommerce.com';
    protected $_status = false;

    protected function initialize() {
      $this->_title = OSCOM::getDef('shipping_flat_title');
      $this->_description = OSCOM::getDef('shipping_flat_description');
      $this->_status = (defined('MODULE_SHIPPING_FLAT_STATUS') && (MODULE_SHIPPING_FLAT_STATUS == 'True') ? true : false);
      $this->_sort_order = (defined('MODULE_SHIPPING_FLAT_SORT_ORDER') ? MODULE_SHIPPING_FLAT_SORT_ORDER : 0);
    }

    public function isInstalled() {
      return defined('MODULE_SHIPPING_FLAT_STATUS');
    }
  }
?>
