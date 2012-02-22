<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;
?>

<h1><?php echo $OSCOM_Template->getIcon(32) . HTML::link(OSCOM::getLink(), $OSCOM_Template->getPageTitle()); ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists() ) {
    echo $OSCOM_MessageStack->get();
  }
?>

<form id="liveSearchForm">
  <?php echo HTML::inputField('search', null, 'id="liveSearchField" class="searchField" placeholder="' . OSCOM::getDef('placeholder_search') . '"') . HTML::button(array('type' => 'button', 'params' => 'onclick="osC_DataTable.reset();"', 'title' => OSCOM::getDef('button_reset'))); ?>

  <span style="float: right;"><?php echo HTML::button(array('href' => OSCOM::getLink(null, null, 'Install'), 'icon' => 'plus', 'title' => OSCOM::getDef('button_install'))); ?></span>
</form>

<div style="padding: 20px 5px 5px 5px; height: 16px;">
  <span id="batchTotalPages"></span>
  <span id="batchPageLinks"></span>
</div>

<form name="batch" action="#" method="post">

<table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTable" id="ordertotalModulesDataTable">
  <thead>
    <tr>
      <th><?php echo OSCOM::getDef('table_heading_order_total_modules'); ?></th>
      <th><?php echo OSCOM::getDef('table_heading_sort_order'); ?></th>
      <th width="150"><?php echo OSCOM::getDef('table_heading_action'); ?></th>
    </tr>
  </thead>
  <tfoot>
    <tr>
      <th colspan="3">&nbsp;</th>
    </tr>
  </tfoot>
  <tbody>
  </tbody>
</table>

</form>

<div style="padding: 5px;">
  <span id="dataTableLegend"><?php echo '<b>' . OSCOM::getDef('table_action_legend') . '</b> ' . HTML::icon('edit.png') . '&nbsp;' . OSCOM::getDef('icon_edit') . '&nbsp;&nbsp;' . HTML::icon('uninstall.png') . '&nbsp;' . OSCOM::getDef('icon_uninstall'); ?></span>
  <span id="batchPullDownMenu"></span>
</div>

<script type="text/javascript">
  var moduleParamsCookieName = 'oscom_admin_' + pageModule;
  var dataTablePageSetName = 'page';

  var moduleParams = new Object();
  moduleParams[dataTablePageSetName] = 1;
  moduleParams['search'] = '';

  if ( $.cookie(moduleParamsCookieName) != null ) {
    moduleParams = $.secureEvalJSON($.cookie(moduleParamsCookieName));
  }

  var dataTableName = 'ordertotalModulesDataTable';
  var dataTableDataURL = '<?php echo OSCOM::getRPCLink(null, null, 'GetInstalled'); ?>';

  var otmEditLink = '<?php echo OSCOM::getLink(null, null, 'Save&code=OTMCODE'); ?>';
  var otmEditLinkIcon = '<?php echo HTML::icon('edit.png'); ?>';

  var otmUninstallLink = '<?php echo OSCOM::getLink(null, null, 'Uninstall&code=OTMCODE'); ?>';
  var otmUninstallLinkIcon = '<?php echo HTML::icon('uninstall.png'); ?>';

  var osC_DataTable = new osC_DataTable();
  osC_DataTable.load();

  function feedDataTable(data) {
    var rowCounter = 0;

    for ( var r in data.entries ) {
      var record = data.entries[r];

      var newRow = $('#' + dataTableName)[0].tBodies[0].insertRow(rowCounter);
      newRow.id = 'row' + record.code;

      if ( record.status != true ) {
        $('#row' + record.code).addClass('deactivatedRow');
      }

      $('#row' + record.code).hover( function() { $(this).addClass('mouseOver'); }, function() { $(this).removeClass('mouseOver'); }).css('cursor', 'pointer');

      var newCell = newRow.insertCell(0);
      newCell.innerHTML = htmlSpecialChars(record.title);

      newCell = newRow.insertCell(1);
      newCell.innerHTML = parseInt(record.sort_order);

      newCell = newRow.insertCell(2);
      newCell.innerHTML = '<a href="' + otmEditLink.replace('OTMCODE', htmlSpecialChars(record.code)) + '">' + otmEditLinkIcon + '</a>&nbsp;<a href="' + otmUninstallLink.replace('OTMCODE', htmlSpecialChars(record.code)) + '">' + otmUninstallLinkIcon + '</a>';
      newCell.align = 'right';

      rowCounter++;
    }
  }
</script>
