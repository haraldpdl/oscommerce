<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2011 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\ObjectInfo;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Site\Admin\Application\Configuration\Configuration;
  use osCommerce\OM\Core\Site\Admin\Application\Services\Services;

  $OSCOM_ObjectInfo = new ObjectInfo(Services::get($_GET['code']));
?>

<h1><?php echo $OSCOM_Template->getIcon(32) . HTML::link(OSCOM::getLink(), $OSCOM_Template->getPageTitle()); ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists() ) {
    echo $OSCOM_MessageStack->get();
  }
?>

<div class="infoBox">
  <h3><?php echo HTML::icon('edit.png') . ' ' . $OSCOM_ObjectInfo->getProtected('title'); ?></h3>

    <div class="servicesDescription"><?php echo '<b>' . OSCOM::getDef('description_of_service') . '</b><br /> ' . htmlspecialchars_decode($OSCOM_ObjectInfo->getProtected('description')); ?></div>
            
  <form name="mEdit" class="dataForm" action="<?php echo OSCOM::getLink(null, null, 'Save&Process&code=' . $OSCOM_ObjectInfo->get('code')); ?>" method="post">

  <p><?php echo OSCOM::getDef('introduction_edit_service_module'); ?></p>

<?php
  $keys = '';

  foreach ( $OSCOM_ObjectInfo->get('keys') as $key ) {
    $key_data = OSCOM::callDB('Admin\Configuration\EntryGet', array('key' => $key));

    $keys .= '<b>' . $key_data['configuration_title'] . '</b><br />' . $key_data['configuration_description'] . '<br />';

    if ( strlen($key_data['set_function']) > 0 ) {
      $keys .= Configuration::callUserFunc($key_data['set_function'], $key_data['configuration_value'], $key);
    } else {
      $keys .= HTML::inputField('configuration[' . $key . ']', $key_data['configuration_value']);
    }

    $keys .= '<br /><br />';
  }

  $keys = substr($keys, 0, strrpos($keys, '<br /><br />'));
?>

  <p><?php echo $keys; ?></p>

  <p><?php echo HTML::button(array('priority' => 'primary', 'icon' => 'check', 'title' => OSCOM::getDef('button_save'))) . ' ' . HTML::button(array('href' => OSCOM::getLink(), 'priority' => 'secondary', 'icon' => 'close', 'title' => OSCOM::getDef('button_cancel'))); ?></p>

  </form>
</div>
