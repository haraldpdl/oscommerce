<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\OrderTotal\SubTotal\Configuration;

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;

/**
 * @since v3.0.4
 */

  class ModuleOrderTotalSubtotalStatus extends \osCommerce\OM\Core\Site\Admin\Module\ConfigurationAbstract {
    protected $_param_true;
    protected $_param_false;

    static protected $_sort = 100;
    static protected $_default = 'true';
    static protected $_group_id = 6;

    public function initialize() {
      $this->_param_true = OSCOM::getDef('parameter_true');
      $this->_param_false = OSCOM::getDef('parameter_false');
    }

    public function get() {
      switch ( $this->getRaw() ) {
        case 'true':
          return $this->_param_true;

        case 'false':
          return $this->_param_false;
      }

      return $this->getRaw();
    }

    public function getField() {
      $values = array(array('id' => 'true',
                            'text' => $this->_param_true),
                      array('id' => 'false',
                            'text' => $this->_param_false));

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
