<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\Configuration;

  use osCommerce\OM\Core\HTML;

/**
 * @since v3.0.4
 */

  class StoreNameAddress extends \osCommerce\OM\Core\Site\Admin\Module\ConfigurationAbstract {
    static protected $_sort = 1800;
    static protected $_group_id = 1;

    public function initialize() { }

    public function getField() {
      return '<label for="cfg' . $this->_module . '">' . $this->getTitle() . '</label>' . HTML::textareaField('configuration[' . $this->_key . ']', $this->getRaw(), 35, 5, 'id="cfg' . $this->_module . '"');
    }

    static public function getDefault() {
      return 'Store Name' . "\n" . 'Address' . "\n" . 'Country' . "\n" . 'Phone';
    }
  }
?>
