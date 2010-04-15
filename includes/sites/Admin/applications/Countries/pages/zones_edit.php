<?php
/*
  osCommerce Online Merchant $osCommerce-SIG$
  Copyright (c) 2010 osCommerce (http://www.oscommerce.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $osC_ObjectInfo = new osC_ObjectInfo(OSCOM_Site_Admin_Application_Countries_Countries::getZone($_GET['zID']));
?>

<h1><?php echo osc_link_object(OSCOM::getLink(), $osC_Template->getPageTitle()); ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists() ) {
    echo $OSCOM_MessageStack->get();
  }
?>

<div class="infoBox">
  <h3><?php echo osc_icon('edit.png') . ' ' . $osC_ObjectInfo->getProtected('zone_name'); ?></h3>

  <form name="zEdit" class="dataForm" action="<?php echo OSCOM::getLink(null, null, 'id=' . $_GET['id'] . '&zID=' . $_GET['zID'] . '&action=ZoneSave'); ?>" method="post">

  <p><?php echo OSCOM::getDef('introduction_edit_zone'); ?></p>

  <fieldset>
    <p><label for="zone_name"><?php echo OSCOM::getDef('field_zone_name'); ?></label><?php echo osc_draw_input_field('zone_name', $osC_ObjectInfo->get('zone_name')); ?></p>
    <p><label for="zone_code"><?php echo OSCOM::getDef('field_zone_code'); ?></label><?php echo osc_draw_input_field('zone_code', $osC_ObjectInfo->get('zone_code')); ?></p>
  </fieldset>

  <p><?php echo osc_draw_hidden_field('subaction', 'confirm') . osc_draw_button(array('priority' => 'primary', 'icon' => 'check', 'title' => OSCOM::getDef('button_save'))) . ' ' . osc_draw_button(array('href' => OSCOM::getLink(null, null, 'id=' . $_GET['id']), 'priority' => 'secondary', 'icon' => 'close', 'title' => OSCOM::getDef('button_cancel'))); ?></p>

  </form>
</div>