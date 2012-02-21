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

  class ModulePaymentPaypalExpressCheckoutStatus extends \osCommerce\OM\Core\Site\Admin\Module\ConfigurationAbstract {
    protected $_param_enabled;
    protected $_param_disabled;

    static protected $_sort = 100;
    static protected $_default = '-1';
    static protected $_group_id = 6;

    public function initialize() {
      $this->_param_enabled = OSCOM::getDef('parameter_enabled');
      $this->_param_disabled = OSCOM::getDef('parameter_disabled');
    }

    public function get() {
      switch ( $this->getRaw() ) {
        case '1':
          return $this->_param_enabled;

        case '-1':
          return $this->_param_disabled;
      }

      return $this->getRaw();
    }

    public function getField() {
      $values = array(array('id' => '1',
                            'text' => $this->_param_enabled),
                      array('id' => '-1',
                            'text' => $this->_param_disabled));

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
