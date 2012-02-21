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

  class ModulePaymentPaypalExpressCheckoutImage extends \osCommerce\OM\Core\Site\Admin\Module\ConfigurationAbstract {
    protected $_param_image_static;
    protected $_param_image_dynamic;

    static protected $_sort = 1000;
    static protected $_default = 'Static';
    static protected $_group_id = 6;

    public function initialize() {
      $this->_param_image_static= OSCOM::getDef('parameter_image_static');
      $this->_param_image_dynamic = OSCOM::getDef('parameter_image_dynamic');
    }

    public function getField() {
      $values = array(array('id' => 'Static',
                            'text' => $this->_param_image_static),
                      array('id' => 'Dynamic',
                            'text' => $this->_param_image_dynamic));

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
