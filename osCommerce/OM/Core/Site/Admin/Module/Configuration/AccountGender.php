<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\Configuration;

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;

/**
 * @since v3.0.4
 */

  class AccountGender extends \osCommerce\OM\Core\Site\Admin\ConfigurationModule {
    protected $_param_required;
    protected $_param_not_required;
    protected $_param_disabled;

    static protected $_sort = 1000;
    static protected $_default = '1';
    static protected $_group_id = 5;

    public function __construct($key, $module = null) {
      parent::__construct($key, $module);

      $this->_param_required = OSCOM::getDef('parameter_required');
      $this->_param_not_required = OSCOM::getDef('parameter_not_required');
      $this->_param_disabled = OSCOM::getDef('parameter_disabled');
    }

    public function get() {
      switch ( $this->getRaw() ) {
        case '1':
          return $this->_param_required;

        case '0':
          return $this->_param_not_required;

        case '-1':
          return $this->_param_disabled;
      }

      return $this->getRaw();
    }

    public function getField() {
      $values = array(array('id' => '1',
                            'text' => $this->_param_required),
                      array('id' => '0',
                            'text' => $this->_param_not_required),
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
