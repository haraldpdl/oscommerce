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

  class ModulePaymentPaypalExpressCheckoutTransactionServer extends \osCommerce\OM\Core\Site\Admin\Module\ConfigurationAbstract {
    protected $_param_server_live;
    protected $_param_server_sandbox;

    static protected $_sort = 600;
    static protected $_default = 'Live';
    static protected $_group_id = 6;

    public function initialize() {
      $this->_param_server_live = OSCOM::getDef('parameter_server_live');
      $this->_param_server_sandbox = OSCOM::getDef('parameter_server_sandbox');
    }

    public function getField() {
      $values = array(array('id' => 'Live',
                            'text' => $this->_param_server_live),
                      array('id' => 'Sandbox',
                            'text' => $this->_param_server_sandbox));

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
