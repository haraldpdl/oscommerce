<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\Payment\PayPalExpressCheckout;

  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

/**
 * The administration side of the Paypal Express Checkout payment module
 *
 * @since v3.0.4
 */

  class Controller extends \osCommerce\OM\Core\Site\Admin\Module\PaymentAbstract {

/**
 * The administrative title of the payment module
 *
 * @var string
 * @access protected
 */

    protected $_title;

/**
 * The administrative description of the payment module
 *
 * @var string
 * @access protected
 */

    protected $_description;

/**
 * The developers name
 *
 * @var string
 * @access protected
 */

    protected $_author_name = 'osCommerce';

/**
 * The developers address
 *
 * @var string
 * @access protected
 */

    protected $_author_www = 'http://www.oscommerce.com';

/**
 * The status of the module
 *
 * @var boolean
 * @access protected
 */

    protected $_status = false;

/**
 * Initialize module
 *
 * @access protected
 */

    protected function initialize() {
      $this->_title = OSCOM::getDef('paypal_express_checkout_title');
      $this->_description = OSCOM::getDef('paypal_express_checkout_description');
      $this->_status = (defined('MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_STATUS') && (MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_SORT_ORDER') ? MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_SORT_ORDER : 0);
    }

/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

    public function isInstalled() {
      return defined('MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_STATUS');
    }
  }
?>
