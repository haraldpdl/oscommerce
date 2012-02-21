<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\Payment\COD;

  use osCommerce\OM\Core\OSCOM;

/**
 * The administration side of the Cash On Delivery payment module
 *
 * @since v3.0.4
 */

  class Controller extends \osCommerce\OM\Core\Site\Admin\Module\PaymentAbstract {

/**
 * The administrative title of the payment module
 *
 * @var string
 */

    protected $_title;

/**
 * The administrative description of the payment module
 *
 * @var string
 */

    protected $_description;

/**
 * The developers name
 *
 * @var string
 */

    protected $_author_name = 'osCommerce';

/**
 * The developers address
 *
 * @var string
 */

    protected $_author_www = 'http://www.oscommerce.com';

/**
 * The status of the module
 *
 * @var boolean
 */

    protected $_status = false;

/**
 * Initialize module
 */

    protected function initialize() {
      $this->_title = OSCOM::getDef('payment_cod_title');
      $this->_description = OSCOM::getDef('payment_cod_description');
      $this->_status = (defined('MODULE_PAYMENT_COD_STATUS') && (MODULE_PAYMENT_COD_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_COD_SORT_ORDER') ? MODULE_PAYMENT_COD_SORT_ORDER : 0);
    }

/**
 * Checks to see if the module has been installed
 *
 * @return boolean
 */

    public function isInstalled() {
      return defined('MODULE_PAYMENT_COD_STATUS');
    }
  }
?>
