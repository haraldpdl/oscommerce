<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2011 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\Payment;

  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

/**
 * The administration side of the Paypal Express Checkout payment module
 */

  class PayPalExpressCheckout extends \osCommerce\OM\Core\Site\Admin\PaymentModuleAbstract {

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

/**
 * Installs the module
 *
 * @access public
 * @see \osCommerce\OM\Core\Site\Admin\PaymentModuleAbstract::install()
 */

    public function install() {
      parent::install();

      $data = array(array('title' => 'Enable PayPal Express Checkout',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_STATUS',
                          'value' => '-1',
                          'description' => 'Do you want to accept PayPal Express Checkout payments?',
                          'group_id' => '6',
                          'use_function' => 'osc_cfg_use_get_boolean_value',
                          'set_function' => 'osc_cfg_set_boolean_value(array(1, -1))'),
                    array('title' => 'Seller Account',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_SELLER_ACCOUNT',
                          'value' => '',
                          'description' => 'The email address of the seller account if no API credentials has been setup.',
                          'group_id' => '6'),
                    array('title' => 'API Username',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_API_USERNAME',
                          'value' => '',
                          'description' => 'The username to use for the PayPal API service',
                          'group_id' => '6'),
                    array('title' => 'API Password',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_API_PASSWORD',
                          'value' => '',
                          'description' => 'The password to use for the PayPal API service',
                          'group_id' => '6'),
                    array('title' => 'API Signature',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_API_SIGNATURE',
                          'value' => '',
                          'description' => 'The signature to use for the PayPal API service',
                          'group_id' => '6'),
                    array('title' => 'Transaction Server',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_TRANSACTION_SERVER',
                          'value' => 'Live',
                          'description' => 'Use the live or testing (sandbox) gateway server to process transactions?',
                          'group_id' => '6',
                          'set_function' => 'osc_cfg_set_boolean_value(array(\'Live\', \'Sandbox\'))'),
                    array('title' => 'Transaction Method',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_TRANSACTION_METHOD',
                          'value' => 'Sale',
                          'description' => 'The processing method to use for each transaction.',
                          'group_id' => '6',
                          'set_function' => 'osc_cfg_set_boolean_value(array(\'Authorization\', \'Sale\'))'),
                    array('title' => 'PayPal Account Optional',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_ACCOUNT_OPTIONAL',
                          'value' => '-1',
                          'description' => 'This must also be enabled in your PayPal account, in Profile > Website Payment Preferences.',
                          'group_id' => '6',
                          'use_function' => 'osc_cfg_use_get_boolean_value',
                          'set_function' => 'osc_cfg_set_boolean_value(array(1, -1))'),
                    array('title' => 'PayPal Instant Update',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_INSTANT_UPDATE',
                          'value' => '1',
                          'description' => 'Support PayPal shipping and tax calculations on the PayPal.com site during Express Checkout.',
                          'group_id' => '6',
                          'use_function' => 'osc_cfg_use_get_boolean_value',
                          'set_function' => 'osc_cfg_set_boolean_value(array(1, -1))'),
                    array('title' => 'PayPal Checkout Image',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_IMAGE',
                          'value' => 'Static',
                          'description' => 'Use static or dynamic Express Checkout image buttons. Dynamic images are used with PayPal campaigns.',
                          'group_id' => '6',
                          'set_function' => 'osc_cfg_set_boolean_value(array(\'Static\', \'Dynamic\'))'),
                    array('title' => 'Debug E-Mail Address',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_DEBUG_EMAIL',
                          'value' => '',
                          'description' => 'All parameters of an invalid transaction will be sent to this email address.',
                          'group_id' => '6'),
                    array('title' => 'Payment Zone',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_ZONE',
                          'value' => '0',
                          'description' => 'If a zone is selected, only enable this payment method for that zone.',
                          'group_id' => '6',
                          'use_function' => 'osc_cfg_use_get_zone_class_title',
                          'set_function' => 'osc_cfg_set_zone_classes_pull_down_menu'),
                    array('title' => 'Sort order of display.',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_SORT_ORDER',
                          'value' => '0',
                          'description' => 'Sort order of display. Lowest is displayed first.',
                          'group_id' => '6'),
                    array('title' => 'Set Order Status',
                          'key' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_ORDER_STATUS_ID',
                          'value' => '0',
                          'description' => 'Set the status of orders made with this payment module to this value',
                          'group_id' => '6',
                          'use_function' => 'osc_cfg_use_get_order_status_title',
                          'set_function' => 'osc_cfg_set_order_statuses_pull_down_menu')
                   );

      OSCOM::callDB('Admin\InsertConfigurationParameters', $data, 'Site');
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

    public function getKeys() {
      return array('MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_STATUS',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_SELLER_ACCOUNT',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_API_USERNAME',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_API_PASSWORD',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_API_SIGNATURE',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_TRANSACTION_SERVER',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_TRANSACTION_METHOD',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_ACCOUNT_OPTIONAL',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_INSTANT_UPDATE',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_IMAGE',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_DEBUG_EMAIL',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_ZONE',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_SORT_ORDER',
                   'MODULE_PAYMENT_PAYPAL_EXPRESS_CHECKOUT_ORDER_STATUS_ID');
    }
  }
?>
