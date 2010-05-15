<?php
/*
  osCommerce Online Merchant $osCommerce-SIG$
  Copyright (c) 2010 osCommerce (http://www.oscommerce.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  namespace osCommerce\OM\Site\Shop\Module\Box\Categories;

  use osCommerce\OM\Registry;
  use osCommerce\OM\OSCOM;

  class Controller extends \osCommerce\OM\Modules {
    var $_title,
        $_code = 'Categories',
        $_author_name = 'osCommerce',
        $_author_www = 'http://www.oscommerce.com',
        $_group = 'Box';

    public function __construct() {
      $this->_title = OSCOM::getDef('box_categories_heading');
    }

    public function initialize() {
      $OSCOM_CategoryTree = Registry::get('CategoryTree');
      $OSCOM_Category = Registry::get('Category');

      $OSCOM_CategoryTree->reset();
      $OSCOM_CategoryTree->setCategoryPath($OSCOM_Category->getPath(), '<b>', '</b>');
      $OSCOM_CategoryTree->setParentGroupString('', '');
      $OSCOM_CategoryTree->setParentString('', '-&gt;');
      $OSCOM_CategoryTree->setChildString('', '<br />');
      $OSCOM_CategoryTree->setSpacerString('&nbsp;', 2);
      $OSCOM_CategoryTree->setShowCategoryProductCount((BOX_CATEGORIES_SHOW_PRODUCT_COUNT == '1') ? true : false);

      $this->_content = $OSCOM_CategoryTree->getTree();
    }

    function install() {
      $OSCOM_Database = Registry::get('Database');

      parent::install();

      $OSCOM_Database->simpleQuery("insert into :table_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Show Product Count', 'BOX_CATEGORIES_SHOW_PRODUCT_COUNT', '1', 'Show the amount of products each category has', '6', '0', 'osc_cfg_use_get_boolean_value', 'osc_cfg_set_boolean_value(array(1, -1))', now())");
    }

    function getKeys() {
      if (!isset($this->_keys)) {
        $this->_keys = array('BOX_CATEGORIES_SHOW_PRODUCT_COUNT');
      }

      return $this->_keys;
    }
  }
?>
