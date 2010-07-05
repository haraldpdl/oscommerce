<?php
/*
  osCommerce Online Merchant $osCommerce-SIG$
  Copyright (c) 2010 osCommerce (http://www.oscommerce.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  namespace osCommerce\OM\Site\Shop\Application\Products\Action\Reviews;

  use osCommerce\OM\ApplicationAbstract;
  use osCommerce\OM\Registry;
  use osCommerce\OM\Site\Shop\Product;
  use osCommerce\OM\OSCOM;

  class Write {
    public static function execute(ApplicationAbstract $application) {
      $OSCOM_Customer = Registry::get('Customer');
      $OSCOM_NavigationHistory = Registry::get('NavigationHistory');
      $OSCOM_Template = Registry::get('Template');
      $OSCOM_Service = Registry::get('Service');
      $OSCOM_Breadcrumb = Registry::get('Breadcrumb');

      $requested_product = null;
      $product_check = false;

      if ( count($_GET) > 3 ) {
        $requested_product = basename(key(array_slice($_GET, 3, 1, true)));

        if ( $requested_product == 'Write' ) {
          unset($requested_product);

          if ( count($_GET) > 4 ) {
            $requested_product = basename(key(array_slice($_GET, 4, 1, true)));
          }
        }
      }

      if ( isset($requested_product) ) {
        if ( Product::checkEntry($requested_product) ) {
          $product_check = true;
        }
      }

      if ( $product_check === false ) {
        $application->setPageContent('not_found.php');

        return false;
      }

      if ( ($OSCOM_Customer->isLoggedOn() === false) && (SERVICE_REVIEW_ENABLE_REVIEWS == 1) ) {
        $OSCOM_NavigationHistory->setSnapshot();

        osc_redirect(OSCOM::getLink(null, 'Account', 'LogIn', 'SSL'));
      }

      Registry::set('Product', new Product($requested_product));
      $OSCOM_Product = Registry::get('Product');

      $application->setPageTitle($OSCOM_Product->getTitle());
      $application->setPageContent('reviews_write.php');
      $OSCOM_Template->addJavascriptPhpFilename('templates/' . $OSCOM_Template->getCode() . '/javascript/products/reviews_new.php');

      if ( $OSCOM_Service->isStarted('Breadcrumb')) {
        $OSCOM_Breadcrumb->add($OSCOM_Product->getTitle(), OSCOM::getLink(null, null, 'Reviews&' . $OSCOM_Product->getKeyword()));
        $OSCOM_Breadcrumb->add(OSCOM::getDef('breadcrumb_reviews_new'), OSCOM::getLink(null, null, 'Reviews&Write&' . $OSCOM_Product->getKeyword()));
      }
    }
  }
?>
