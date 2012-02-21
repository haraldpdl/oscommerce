<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\Payment\PayPalExpressCheckout\Configuration;

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;

/**
 * @since v3.0.4
 */

  class ModulePaymentPaypalExpressCheckoutTransactionMethod extends \osCommerce\OM\Core\Site\Admin\Module\ConfigurationAbstract {
    protected $_param_trans_authorization;
    protected $_param_trans_sale;

    static protected $_sort = 700;
    static protected $_default = 'Sale';
    static protected $_group_id = 6;

    public function initialize() {
      $this->_param_trans_authorization = OSCOM::getDef('parameter_trans_authorization');
      $this->_param_trans_sale = OSCOM::getDef('parameter_trans_sale');
    }

    public function getField() {
      $values = array(array('id' => 'Authorization',
                            'text' => $this->_param_trans_authorization),
                      array('id' => 'Sale',
                            'text' => $this->_param_trans_sale));

      $field = '<h4>' . $this->getTitle() . '</h4><div id="cfg' . $this->_module . '">' . HTML::radioField('configuration[' . $this->_key . ']', $values, $this->getRaw()) . '</div>';

      $field .= <<<EOT
<script>
$('#cfg{$this->_module}').buttonset();
</script>
EOT;

      return $field;
    }
  }
?>
