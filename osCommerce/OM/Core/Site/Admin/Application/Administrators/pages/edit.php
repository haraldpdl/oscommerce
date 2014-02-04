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
  use osCommerce\OM\Core\Site\Admin\Application\Administrators\Administrators;

  $OSCOM_ObjectInfo = new ObjectInfo(Administrators::get($_GET['id']));
?>

<h1><?php echo $OSCOM_Template->getIcon(32) . HTML::link(OSCOM::getLink(), $OSCOM_Template->getPageTitle()); ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists() ) {
    echo $OSCOM_MessageStack->get();
  }
?>

<div class="infoBox">
  <h3><?php echo HTML::icon('edit.png') . ' ' . $OSCOM_ObjectInfo->getProtected('user_name'); ?></h3>

  <form name="aEdit" class="dataForm" autocomplete="off" action="<?php echo OSCOM::getLink(null, null, 'Save&Process&id=' . $OSCOM_ObjectInfo->getInt('id')); ?>" method="post">

  <p><?php echo OSCOM::getDef('introduction_edit_administrator'); ?></p>

  <fieldset>
    <p><label for="user_name"><?php echo OSCOM::getDef('field_username'); ?></label><?php echo HTML::inputField('user_name', $OSCOM_ObjectInfo->get('user_name')); ?></p>
    <p><label for="user_password"><?php echo OSCOM::getDef('field_password'); ?></label><?php echo HTML::passwordField('user_password'); ?></p>

    <p><select name="accessModules" id="modulesList"><option value="-1" disabled="disabled">-- Access Modules --</option><option value="0"><?php echo OSCOM::getDef('global_access'); ?></option></select></p>

    <ul id="accessToModules" class="modulesListing"></ul>
  </fieldset>

  <p><?php echo HTML::button(array('priority' => 'primary', 'icon' => 'check', 'title' => OSCOM::getDef('button_save'))) . ' ' . HTML::button(array('href' => OSCOM::getLink(), 'priority' => 'secondary', 'icon' => 'close', 'title' => OSCOM::getDef('button_cancel'))); ?></p>

  </form>
</div>

<script type="text/javascript">
  var accessModules = <?php echo json_encode(Administrators::getAccessModules()); ?>;
  var hasAccessTo = <?php echo json_encode($OSCOM_ObjectInfo->get('access_modules')); ?>;
  var deleteAccessModuleIcon = '<?php echo HTML::icon('uninstall.png'); ?>';

  var $modulesList = $('#modulesList');

  $.each(accessModules, function(i, item) {
    var sGroup = document.createElement('optgroup');
    sGroup.label = i;

    $.each(item, function(key, value) {
      var sOption = new Option(value['text'], value['id']);
      sOption.id = 'am' + value['id'];

      sGroup.appendChild(sOption);

      if ( $.inArray(value['id'], hasAccessTo) != -1 ) {
        $('#accessToModules').append('<li id="atm' + value['id'] + '">' + i + ' &raquo; ' + value['text'] + ' <span style="float: right;"><a href="#" onclick="removeAccessToModule(\'' + value['id'] + '\');">' + deleteAccessModuleIcon + '</a><input type="hidden" name="modules[]" value="' + value['id'] + '" /></span></li>');
        sOption.disabled = 'disabled';
      }
    });

    $modulesList.append(sGroup); 
  });
  
  if(hasAccessTo.length == 0){ 
      $('#modulesList :first').attr('selected', 'selected');
  }
 
  if ( $.inArray('*', hasAccessTo) != -1 ) {
    $('#modulesList').val('0');

    $('#accessToModules').append('<li id="atm' + $('#modulesList :selected').val() + '">' + $('#modulesList :selected').text() + ' <span style="float: right;"><a href="#" onclick="removeAccessToModule(\'' + $('#modulesList :selected').val() + '\');">' + deleteAccessModuleIcon + '</a><input type="hidden" name="modules[]" value="' + $('#modulesList :selected').val() + '" /></span></li>');

    $('#modulesList').attr('disabled', 'disabled');
    $('#modulesList').val('-1');
  }

  $('#modulesList').change(function() {
    if ( $('#modulesList :selected').val() == '0' ) {
      $('#accessToModules li').remove();
      $('#accessToModules').append('<li id="atm' + $('#modulesList :selected').val() + '">' + $('#modulesList :selected').text() + ' <span style="float: right;"><a href="#" onclick="removeAccessToModule(\'' + $('#modulesList :selected').val() + '\');">' + deleteAccessModuleIcon + '</a><input type="hidden" name="modules[]" value="' + $('#modulesList :selected').val() + '" /></span></li>');

      $('#modulesList').attr('disabled', 'disabled');
      $('#modulesList').val('-1');

      $('#accessToModules li').tsort();
    } else if ( $('#modulesList :selected').val() != '-1' ) {
      $('#accessToModules').append('<li id="atm' + $('#modulesList :selected').val() + '">' + $('#modulesList :selected').parent().attr('label') + ' &raquo; ' + $('#modulesList :selected').text() + ' <span style="float: right;"><a href="#" onclick="removeAccessToModule(\'' + $('#modulesList :selected').val() + '\');">' + deleteAccessModuleIcon + '</a><input type="hidden" name="modules[]" value="' + $('#modulesList :selected').val() + '" /></span></li>');

      $('#modulesList :selected').attr('disabled', 'disabled');
      $('#modulesList').val('-1');

      $('#accessToModules li').tsort();
    }
  });

  function removeAccessToModule(module) {
    if ( module == '0' ) {
      $('#modulesList').removeAttr('disabled');
      $('#modulesList :disabled').removeAttr('disabled');
      $('#modulesList :first').attr('disabled', 'disabled');
    } else {
      $('#am' + module).removeAttr('disabled');
    }

    $('#atm' + module).remove();
  }
</script>
