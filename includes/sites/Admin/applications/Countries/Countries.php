<?php
/*
  osCommerce Online Merchant $osCommerce-SIG$
  Copyright (c) 2010 osCommerce (http://www.oscommerce.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class OSCOM_Site_Admin_Application_Countries extends OSCOM_Site_Admin_ApplicationAbstract {
    protected function initialize() {
      $this->_page_title = OSCOM::getDef('heading_title');

      if ( !empty($_GET['id']) && is_numeric($_GET['id']) ) {
        $this->_page_title .= ': ' . OSCOM_Site_Admin_Application_Countries_Countries::get($_GET['id'], 'countries_name');
        $this->_page_contents = 'zones.php';
      }
    }
  }
?>