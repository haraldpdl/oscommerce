<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\ObjectInfo;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Site\Admin\Application\Configuration\Configuration;
  use osCommerce\OM\Core\Site\Admin\Application\OrderTotalModules\OrderTotalModules;

  $OSCOM_ObjectInfo = new ObjectInfo(OrderTotalModules::get($_GET['code']));
?>

<h1><?php echo $OSCOM_Template->getIcon(32) . HTML::link(OSCOM::getLink(), $OSCOM_Template->getPageTitle()); ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists() ) {
    echo $OSCOM_MessageStack->get();
  }
?>

<div class="infoBox">
  <h3><?php echo HTML::icon('edit.png') . ' ' . $OSCOM_ObjectInfo->getProtected('title'); ?></h3>

  <form name="otmEdit" class="dataForm" action="<?php echo OSCOM::getLink(null, null, 'Save&Process&code=' . $OSCOM_ObjectInfo->get('code')); ?>" method="post">

  <p><?php echo OSCOM::getDef('introduction_edit_order_total_module'); ?></p>

  <fieldset>

<?php
  foreach ( $OSCOM_ObjectInfo->get('keys') as $key ) {
    $Qkey = $OSCOM_PDO->prepare('select configuration_id from :table_configuration where configuration_key = :configuration_key');
    $Qkey->bindValue(':configuration_key', $key);
    $Qkey->execute();

    $OSCOM_ConfigObjectInfo = new ObjectInfo(Configuration::getEntry($Qkey->valueInt('configuration_id'), null, 'Admin\\Module\\OrderTotal\\' . $OSCOM_ObjectInfo->get('code')));
?>

    <p><?php echo $OSCOM_ConfigObjectInfo->get('configuration_field'); ?></p>
    <p><?php echo $OSCOM_ConfigObjectInfo->get('configuration_description'); ?></p>

<?php
  }
?>

  </fieldset>

  <p><?php echo HTML::button(array('priority' => 'primary', 'icon' => 'check', 'title' => OSCOM::getDef('button_save'))) . ' ' . HTML::button(array('href' => OSCOM::getLink(), 'priority' => 'secondary', 'icon' => 'close', 'title' => OSCOM::getDef('button_cancel'))); ?></p>

  </form>
</div>
