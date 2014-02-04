<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2011 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Site\Admin\Application\Customers\Customers;
  use osCommerce\OM\Core\Site\Shop\Address;
?>

<div id="sectionMenu_addressBook">
  <div class="infoBox">

<?php
  if ( $new_customer ) {
    echo '<h3>' . HTML::icon('new.png') . ' ' . OSCOM::getDef('action_heading_new_customer') . '</h3>';
  } else {
    echo '<h3>' . HTML::icon('edit.png') . ' ' . $OSCOM_ObjectInfo->getProtected('customers_name') . '</h3>';
  }
?>

    <ul style="margin: 0; padding: 0; list-style: none;">

<?php
  if ( $new_customer === false ) {
    $address_fields = '';

    foreach ( Customers::getAddressBook($_GET['id']) as $ab ) {
      $address_fields .= HTML::hiddenField('ab[' . $ab['address_book_id'] . '][id]', $ab['address_book_id']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][gender]', $ab['gender']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][firstname]', $ab['firstname']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][lastname]', $ab['lastname']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][company]', $ab['company']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][street_address]', $ab['street_address']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][suburb]', $ab['suburb']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][city]', $ab['city']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][postcode]', $ab['postcode']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][state]', $ab['state']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][zone_id]', $ab['zone_id']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][country_id]', $ab['country_id']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][telephone]', $ab['telephone_number']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][fax]', $ab['fax_number']) .
                         HTML::hiddenField('ab[' . $ab['address_book_id'] . '][changed]', 'false');

      $address_string = '<div class="abLabel">';

      if ( in_array($ab['gender'], array('m', 'f')) ) {
        $address_string .= '<div style="float: right; background: url(\'';

        if ( $ab['gender'] == 'm' ) {
          $address_string .= HTML::iconRaw('user_male.png', '32x32');
        } else {
          $address_string .= HTML::iconRaw('user_female.png', '32x32');
        }

        $address_string .= '\') no-repeat; opacity: 0.5; filter: alpha(opacity=50); width: 32px; height: 32px;"></div>';
      }

      $address_string .= Address::format($ab, '<br />');

      if ( !empty($ab['telephone_number']) || !empty($ab['fax_number']) ) {
        $address_string .= '<br /><br />';

        if ( !empty($ab['telephone_number']) ) {
          $address_string .= HTML::icon('telephone.png', null, null, 'style="margin-right: 6px;"') . HTML::outputProtected($ab['telephone_number']);
        }

        if ( !empty($ab['telephone_number']) && !empty($ab['fax_number']) ) {
          $address_string .= '<br />';
        }

        if ( !empty($ab['fax_number']) ) {
          $address_string .= HTML::icon('fax.png', null, null, 'style="margin-right: 6px;"') . HTML::outputProtected($ab['fax_number']);
        }
      }

      $address_string .= '</div>';

      $address_string .= '<div style="clear: both;"></div>';

      $address_string .= '<div class="abActions" style="float: right;"><span class="default"><a href="#" onclick="showEditAddressForm(\'' . $ab['address_book_id'] . '\'); return false;">' . HTML::icon('edit.png') . '</a>&nbsp;<a href="#" onclick="deleteAddress(\'' . $ab['address_book_id'] . '\'); return false;">' . HTML::icon('trash.png') . '</a></span></div>';

      echo '      <li id="abEntry' . $ab['address_book_id'] . '" style="float: left; margin: 10px; padding: 10px; border: 1px solid #999; background-color: #fff; box-shadow: 4px 4px 8px #ccc;">' . $address_string . '</li>';
    }
  }
?>

      <li style="float: left; margin: 10px; padding: 10px; border: 1px solid #999; background-color: #e6f1f6; box-shadow: 4px 4px 8px #ccc; text-align: center;"><a href="#" onclick="showNewAddressForm(); return false;"><?php echo HTML::output(OSCOM::getDef('link_new_address')); ?></a></li>
    </ul>

    <div style="clear: both; padding: 5px;"></div>
  </div>
</div>

<?php
  if ( $new_customer === false ) {
    echo $address_fields . HTML::hiddenField('ab_default_id', $OSCOM_ObjectInfo->get('customers_default_address_id'));
  }
?>

<div id="dialogDeleteAddress" title="<?php echo HTML::output(OSCOM::getDef('dialog_delete_address_title')); ?>">
  <p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span><?php echo OSCOM::getDef('dialog_delete_address_desc'); ?></p>
</div>

<div id="dialogDeleteNewAddress" title="<?php echo HTML::output(OSCOM::getDef('dialog_delete_new_address_title')); ?>">
  <p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span><?php echo OSCOM::getDef('dialog_delete_new_address_desc'); ?></p>
</div>

<div id="dialogDeleteDefaultAddress" title="<?php echo HTML::output(OSCOM::getDef('dialog_delete_default_address_title')); ?>">
  <p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span><?php echo OSCOM::getDef('dialog_delete_default_address_desc'); ?></p>
</div>

<div id="addressBookForm" style="display: none;">
  <div class="infoBox">

<?php
  if ( $new_customer ) {
    echo '<h3>' . HTML::icon('new.png') . ' ' . OSCOM::getDef('action_heading_new_customer') . '</h3>';
  } else {
    echo '<h3>' . HTML::icon('edit.png') . ' ' . $OSCOM_ObjectInfo->getProtected('customers_name') . '</h3>';
  }
?>

    <fieldset>

<?php
  if ( ACCOUNT_GENDER > -1 ) {
?>

    <p id="abGenderFields"><label for="ab_gender"><?php echo OSCOM::getDef('field_gender'); ?></label><?php echo HTML::radioField('ab_gender', $gender_array, 'm', null, ''); ?></p>

<script>
$('input[name="ab_gender"]').removeAttr('checked');
$('input[name="ab_gender"]').filter('[value="' + $('input[name="gender"]:checked').val() + '"]').attr('checked', true);
$('#abGenderFields').buttonset();
</script>

<?php
  }
?>

    <p><label for="ab_firstname"><?php echo OSCOM::getDef('field_first_name'); ?></label><?php echo HTML::inputField('ab_firstname'); ?></p>
    <p><label for="ab_lastname"><?php echo OSCOM::getDef('field_last_name'); ?></label><?php echo HTML::inputField('ab_lastname'); ?></p>

<script>
$('#ab_firstname').val($('#firstname').val());
$('#ab_lastname').val($('#lastname').val());
</script>

<?php
  if ( ACCOUNT_COMPANY > -1 ) {
?>

    <p><label for="ab_company"><?php echo OSCOM::getDef('field_company'); ?></label><?php echo HTML::inputField('ab_company'); ?></p>

<?php
  }
?>

    <p><label for="ab_street_address"><?php echo OSCOM::getDef('field_street_address'); ?></label><?php echo HTML::inputField('ab_street_address'); ?></p>

<?php
  if ( ACCOUNT_SUBURB > -1 ) {
?>

    <p><label for="ab_suburb"><?php echo OSCOM::getDef('field_suburb'); ?></label><?php echo HTML::inputField('ab_suburb'); ?></p>

<?php
  }
?>

    <p><label for="ab_postcode"><?php echo OSCOM::getDef('field_post_code'); ?></label><?php echo HTML::inputField('ab_postcode'); ?></p>
    <p><label for="ab_city"><?php echo OSCOM::getDef('field_city'); ?></label><?php echo HTML::inputField('ab_city'); ?></p>

<?php
  $countries_array = array();

  foreach ( Address::getCountries() as $country ) {
    $countries_array[] = array('id' => $country['id'],
                               'text' => $country['name']);
  }
?>

    <p><label for="ab_country"><?php echo OSCOM::getDef('field_country'); ?></label><?php echo HTML::selectMenu('ab_country', $countries_array, STORE_COUNTRY); ?></p>

<?php
  if ( ACCOUNT_STATE > -1 ) {
    if ( Address::hasZones(STORE_COUNTRY) ) {
      $zones_array = array();

      foreach ( Address::getZones(STORE_COUNTRY) as $zone ) {
        $zones_array[] = array('id' => $zone['id'],
                               'text' => $zone['name']);
      }
?>

    <p><label for="ab_state"><?php echo OSCOM::getDef('field_state'); ?></label><?php echo HTML::selectMenu('ab_state', $zones_array); ?></p>

<?php
    } else {
?>

    <p><label for="ab_state"><?php echo OSCOM::getDef('field_state'); ?></label><?php echo HTML::inputField('ab_state'); ?></p>

<?php
    }
  }

  if ( ACCOUNT_TELEPHONE > -1 ) {
?>

    <p><label for="ab_telephone"><?php echo OSCOM::getDef('field_telephone_number'); ?></label><?php echo HTML::inputField('ab_telephone'); ?></p>

<?php
  }

  if ( ACCOUNT_FAX > -1 ) {
?>

    <p><label for="ab_fax"><?php echo OSCOM::getDef('field_fax_number'); ?></label><?php echo HTML::inputField('ab_fax'); ?></p>

<?php
  }
?>

    <p><label for="ab_default"><?php echo OSCOM::getDef('field_set_as_primary'); ?></label><?php echo HTML::checkboxField('ab_default'); ?></p>

    </fieldset>

    <p id="abSubmitButtonsNew"><?php echo HTML::button(array('type' => 'button', 'params' => 'onclick="processAddress();"', 'priority' => 'primary', 'icon' => 'plus', 'title' => OSCOM::getDef('button_add'))) . ' ' . HTML::button(array('type' => 'button', 'params' => 'onclick="cancelAddressForm();"', 'priority' => 'secondary', 'icon' => 'close', 'title' => OSCOM::getDef('button_cancel'))); ?></p>
    <p id="abSubmitButtonsEdit"><?php echo HTML::button(array('type' => 'button', 'params' => 'data-abButtonType="henrysBucket"', 'priority' => 'primary', 'icon' => 'arrowrefresh-1-n', 'title' => OSCOM::getDef('button_update'))) . ' ' . HTML::button(array('type' => 'button', 'params' => 'onclick="cancelAddressForm();"', 'priority' => 'secondary', 'icon' => 'close', 'title' => OSCOM::getDef('button_cancel'))); ?></p>
  </div>
</div>

<script>
$(function() {
  $('#ab_country').change(function() {
    var zone_id, state;

    if ( $(this).data('zone_id') ) {
      zone_id = $(this).data('zone_id');

      $(this).removeData('zone_id');
    } else {
      zone_id = null;
    }

    if ( $(this).data('state') ) {
      state = $(this).data('state');

      $(this).removeData('state');
    } else {
      state = null;
    }

    $.getJSON('<?php echo OSCOM::getRPCLink(null, null, 'GetZones'); ?>', { country_id: $('#ab_country').val() }, function (response) {
      if ( response.rpcStatus == 1 ) {
        var len = response.zones.length;

        if ( len > 0 ) {
          var html = '';

          $('#ab_state').replaceWith('<?php echo HTML::selectMenu('ab_state', array()); ?>');

          for (var i = 0; i < len; i++) {
            html += '<option value="' + response.zones[i].id + '">' + response.zones[i].name + '</option>';
          }

          $('#ab_state').append(html);

          if ( zone_id > 0 ) {
            $('#ab_state').val(zone_id);
          }
        } else {
          $('#ab_state').replaceWith('<?php echo HTML::inputField('ab_state'); ?>');

          if ( state.length > 0 ) {
            $('#ab_state').val(state);
          }
        }
      } else {
        alert('Error: The country zones list could not be populated.'); // HPDL improve
      }
    });
  });
});

var gender_male_icon = '<?php echo HTML::iconRaw('user_male.png', '32x32'); ?>';
var gender_female_icon = '<?php echo HTML::iconRaw('user_female.png', '32x32'); ?>';
var telephone_icon = '<?php echo HTML::icon('telephone.png', null, null, 'style="margin-right: 6px;"'); ?>';
var fax_icon = '<?php echo HTML::icon('fax.png', null, null, 'style="margin-right: 6px;"'); ?>';
var edit_icon = '<?php echo HTML::icon('edit.png'); ?>';
var trash_icon = '<?php echo HTML::icon('trash.png'); ?>';
var undo_icon = '<?php echo HTML::icon('undo.png'); ?>';

var ab_default_marker = '<div class="abDefault" style="float: left;"><?php echo HTML::icon('default.png', OSCOM::getDef('primary_address')); ?></div>';
var ab_default_orig = 'abEntry' + $('input[name="ab_default_id"]').val();
var ab_default = ab_default_orig;

$('#' + ab_default).append(ab_default_marker);

function showNewAddressForm() {
  $('#sectionMenuContainer').animate({'opacity': '0.30'}, 'fast').children().attr('disabled', true);
  $('#formButtons').animate({'opacity': '0.30'}, 'fast').children().attr('disabled', true);

  $('#sectionMenu_addressBook').hide();

  $('#addressBookForm #abSubmitButtonsNew').show();
  $('#addressBookForm #abSubmitButtonsEdit').hide();
    
  $('#addressBookForm').show();
}

function showEditAddressForm(id) {
  $('#sectionMenuContainer').animate({'opacity': '0.30'}, 'fast').children().attr('disabled', true);
  $('#formButtons').animate({'opacity': '0.30'}, 'fast').children().attr('disabled', true);

  $('#sectionMenu_addressBook').hide();
  $('#addressBookForm').show();

  $('#ab_firstname').val($('input[name="ab[' + id + '][firstname]"]').val());
  $('#ab_lastname').val($('input[name="ab[' + id + '][lastname]"]').val());
  $('#ab_street_address').val($('input[name="ab[' + id + '][street_address]"]').val());
  $('#ab_postcode').val($('input[name="ab[' + id + '][postcode]"]').val());
  $('#ab_city').val($('input[name="ab[' + id + '][city]"]').val());
  $('#ab_country').val($('input[name="ab[' + id + '][country_id]"]').val()).data('zone_id', $('input[name="ab[' + id + '][zone_id]"]').val()).data('state', $('input[name="ab[' + id + '][state]"]').val()).change();

  if ( $('input[name="ab_gender"]:checked').length > 0 ) {
    $('input[name="ab_gender"]').removeAttr('checked');
    $('input[name="ab_gender"]').filter('[value="' + $('input[name="ab[' + id + '][gender]"]').val() + '"]').attr('checked', true);
    $('#abGenderFields').buttonset('refresh');
  }

  if ( $('#ab_company').length > 0 ) {
    $('#ab_company').val($('input[name="ab[' + id + '][company]"]').val());
  }

  if ( $('#ab_suburb').length > 0 ) {
    $('#ab_suburb').val($('input[name="ab[' + id + '][suburb]"]').val());
  }

  if ( $('#ab_telephone').length > 0 ) {
    $('#ab_telephone').val($('input[name="ab[' + id + '][telephone]"]').val());
  }

  if ( $('#ab_fax').length > 0 ) {
    $('#ab_fax').val($('input[name="ab[' + id + '][fax]"]').val());
  }

  if ( $('input[name="ab_default_id"]').val() == id ) {
    $('#ab_default').attr({ checked: true, disabled: true });
  }

  $('#addressBookForm #abSubmitButtonsNew').hide();

// Replace onclick handler to pass the address book id as a function parameter
  $('#addressBookForm #abSubmitButtonsEdit button[data-abButtonType="henrysBucket"]').off('click').click(function() {
    processAddress(id);
  });

  $('#addressBookForm #abSubmitButtonsEdit').show();
}

var newAddressCounter = 1;

function processAddress(id) {
  var data = {
    firstname: $('#ab_firstname').val(),
    lastname: $('#ab_lastname').val(),
    street_address: $('#ab_street_address').val(),
    postcode: $('#ab_postcode').val(),
    city: $('#ab_city').val(),
    country_id: $('#ab_country').val()
  };

  if ( $('#ab_company').length > 0 ) {
    data['company'] = $('#ab_company').val();
  }

  if ( $('#ab_suburb').length > 0 ) {
    data['suburb'] = $('#ab_suburb').val();
  }

  if ( $('#ab_state').length > 0 ) {
    if ( $('#ab_state option:selected').length > 0 ) {
      data['zone_id'] = $('#ab_state').val();
      data['state'] = '';
    } else {
      data['zone_id'] = '';
      data['state'] = $('#ab_state').val();
    }
  }

  $.getJSON('<?php echo OSCOM::getRPCLink(null, null, 'FormatAddress'); ?>', data, function (response) {
    if ( response.rpcStatus == 1 ) {
      if ( typeof id === 'undefined' ) {
        var hiddenFields = '<input type="hidden" name="new_address[' + newAddressCounter + '][firstname]" value="' + $('#ab_firstname').val() + '" />' +
                           '<input type="hidden" name="new_address[' + newAddressCounter + '][lastname]" value="' + $('#ab_lastname').val() + '" />' +
                           '<input type="hidden" name="new_address[' + newAddressCounter + '][street_address]" value="' + $('#ab_street_address').val() + '" />' +
                           '<input type="hidden" name="new_address[' + newAddressCounter + '][postcode]" value="' + $('#ab_postcode').val() + '" />' +
                           '<input type="hidden" name="new_address[' + newAddressCounter + '][city]" value="' + $('#ab_city').val() + '" />' +
                           '<input type="hidden" name="new_address[' + newAddressCounter + '][country_id]" value="' + $('#ab_country').val() + '" />';

        if ( $('input[name="ab_gender"]:checked').length > 0 ) {
          hiddenFields += '<input type="hidden" name="new_address[' + newAddressCounter + '][gender]" value="' + $('input[name="ab_gender"]:checked').val() + '" />';
        }

        if ( $('#ab_company').length > 0 ) {
          hiddenFields += '<input type="hidden" name="new_address[' + newAddressCounter + '][company]" value="' + $('#ab_company').val() + '" />';
        }

        if ( $('#ab_suburb').length > 0 ) {
          hiddenFields += '<input type="hidden" name="new_address[' + newAddressCounter + '][suburb]" value="' + $('#ab_suburb').val() + '" />';
        }

        if ( $('#ab_state').length > 0 ) {
          if ( $('#ab_state option:selected').length > 0 ) {
            hiddenFields += '<input type="hidden" name="new_address[' + newAddressCounter + '][zone_id]" value="' + $('#ab_state').val() + '" />' +
                            '<input type="hidden" name="new_address[' + newAddressCounter + '][state]" value="" />';
          } else {
            hiddenFields += '<input type="hidden" name="new_address[' + newAddressCounter + '][zone_id]" value="" />' +
                            '<input type="hidden" name="new_address[' + newAddressCounter + '][state]" value="' + $('#ab_state').val() + '" />';
          }
        }

        if ( $('#ab_telephone').length > 0 ) {
          hiddenFields += '<input type="hidden" name="new_address[' + newAddressCounter + '][telephone]" value="' + $('#ab_telephone').val() + '" />';
        }

        if ( $('#ab_fax').length > 0 ) {
          hiddenFields += '<input type="hidden" name="new_address[' + newAddressCounter + '][fax]" value="' + $('#ab_fax').val() + '" />';
        }

        if ( $('#ab_default').is(':checked') ) {
          hiddenFields += '<input type="hidden" name="new_address[' + newAddressCounter + '][default]" value="true" />';

          $('input[name="ab_default_id"]').val('');
        }
      } else {
        $('input[name="ab[' + id + '][firstname]"]').val($('#ab_firstname').val());
        $('input[name="ab[' + id + '][lastname]"]').val($('#ab_lastname').val());
        $('input[name="ab[' + id + '][street_address]"]').val($('#ab_street_address').val());
        $('input[name="ab[' + id + '][postcode]"]').val($('#ab_postcode').val());
        $('input[name="ab[' + id + '][city]"]').val($('#ab_city').val());
        $('input[name="ab[' + id + '][country_id]"]').val($('#ab_country').val());

        if ( $('input[name="ab_gender"]:checked').length > 0 ) {
          $('input[name="ab[' + id + '][gender]"]').val($('input[name="ab_gender"]:checked').val());
        }

        if ( $('#ab_company').length > 0 ) {
          $('input[name="ab[' + id + '][company]"]').val($('#ab_company').val());
        }

        if ( $('#ab_suburb').length > 0 ) {
          $('input[name="ab[' + id + '][suburb]"]').val($('#ab_suburb').val());
        }

        if ( $('#ab_state').length > 0 ) {
          if ( $('#ab_state option:selected').length > 0 ) {
            $('input[name="ab[' + id + '][zone_id]"]').val($('#ab_state').val());
            $('input[name="ab[' + id + '][state]"]').val('');
          } else {
            $('input[name="ab[' + id + '][zone_id]"]').val('');
            $('input[name="ab[' + id + '][state]"]').val($('#ab_state').val());
          }
        }

        if ( $('#ab_telephone').length > 0 ) {
          $('input[name="ab[' + id + '][telephone]"]').val($('#ab_telephone').val());
        }

        if ( $('#ab_fax').length > 0 ) {
          $('input[name="ab[' + id + '][fax]"]').val($('#ab_fax').val());
        }

        if ( $('#ab_default').is(':checked') ) {
          $('input[name="ab_default_id"]').val(id);
        }

        $('input[name="ab[' + id + '][changed]"]').val('true');
      }

      var address_string = '<div class="abLabel">';

      if ( $('input[name="ab_gender"]:checked').val() == 'm' || $('input[name="ab_gender"]:checked').val() == 'f' ) {
        address_string += '<div style="float: right; background: url(\'';

        if ( $('input[name="ab_gender"]:checked').val() == 'm') {
          address_string += gender_male_icon;
        } else {
          address_string += gender_female_icon;
        }

        address_string += '\') no-repeat; opacity: 0.5; filter: alpha(opacity=50); width: 32px; height: 32px;"></div>';
      }

      address_string += response.address;

      if ( (($('#ab_telephone').length > 0) && ($('#ab_telephone').val().length > 0)) || (($('#ab_fax').length > 0) && ($('#ab_fax').val().length > 0)) ) {
        address_string += '<br /><br />';

        if ( $('#ab_telephone').val().length > 0 ) {
          address_string += telephone_icon + $('#ab_telephone').val();
        }

        if ( $('#ab_telephone').val().length > 0 && $('#ab_fax').val().length > 0 ) {
          address_string += '<br />';
        }

        if ( $('#ab_fax').val().length > 0 ) {
          address_string += fax_icon + $('#ab_fax').val();
        }
      }

      address_string += '</div>';

      address_string += '<div style="clear: both;"></div>';

      if ( typeof id === 'undefined' ) {
        address_string += '<div style="float: right;><a href="#" onclick="$(\'#dialogDeleteNewAddress\').data(\'id\', \'' + newAddressCounter + '\').dialog(\'open\'); return false;">' + trash_icon + '</a></div>';

        address_string += hiddenFields;

        $('#sectionMenu_addressBook ul li:last-child').before('<li id="newAB' + newAddressCounter + '" style="float: left; margin: 10px; padding: 10px; border: 1px solid #999; background-color: #dcfdd7; box-shadow: 4px 4px 8px #ccc;">' + address_string + '</li>');

// Manually register a change in the jQuery safetynet plugin
        $.safetynet.raiseChange('newAB' + newAddressCounter);

        if ( $('#ab_default').is(':checked') ) {
          $('#' + ab_default + ' .abDefault').remove();
          ab_default = 'newAB' + newAddressCounter;
          $('#' + ab_default).append(ab_default_marker);

// Manually register a change in the jQuery safetynet plugin
          $.safetynet.raiseChange('abDefault');
        }

        newAddressCounter++;
      } else {
        $('#abEntry' + id + ' .abLabel').html(address_string);
        $('#abEntry' + id).css('backgroundColor', '#dcfdd7');

// Manually register a change in the jQuery safetynet plugin
        $.safetynet.raiseChange('editAB' + id);

        if ( $('#ab_default').is(':checked') ) {
          $('#' + ab_default + ' .abDefault').remove();
          ab_default = 'abEntry' + id;

// Manually register a change in the jQuery safetynet plugin
          $.safetynet.raiseChange('abDefault');
        }

        if ( $('#' + ab_default + ' .abDefault').length < 1 ) {
          $('#' + ab_default).append(ab_default_marker);
        }
      }
    } else {
      alert('Error: The new address could not be added.'); // HPDL improve
    }

    cancelAddressForm();
  });
}

function cancelAddressForm() {
  $('#addressBookForm').hide();

// reset fields
  $('input[name="ab_gender"]').removeAttr('checked');
  $('input[name="ab_gender"]').filter('[value="' + $('input[name="gender"]:checked').val() + '"]').attr('checked', true);
  $('#abGenderFields').buttonset('refresh');
  $('#ab_firstname').val($('#firstname').val());
  $('#ab_lastname').val($('#lastname').val());
  $('#ab_street_address,#ab_postcode,#ab_city,#ab_company,#ab_suburb,#ab_telephone,#ab_fax').val('');
  $('#ab_country').val('<?php echo STORE_COUNTRY; ?>').change();
  $('#ab_default').removeAttr('checked disabled');

  $('#sectionMenu_addressBook').show();
  $('#sectionMenu_addressBook ul').equalResize();

  $('#formButtons').animate({'opacity': '1'}, 'fast').children().removeAttr('disabled');
  $('#sectionMenuContainer').animate({'opacity': '1'}, 'fast').children().removeAttr('disabled');
}

function deleteAddress(id) {
  if ( ab_default == 'abEntry' + id ) {
    $('#dialogDeleteDefaultAddress').dialog('open');
  } else {
    $('#dialogDeleteAddress').data('id', id).dialog('open');
  }
}

function undoDeleteAddress(id) {
  $('#abDelete' + id).remove();

  $('#abEntry' + id + ' .abActions .undo').remove();
  $('#abEntry' + id + ' .abActions .default').show();
  $('#abEntry' + id).css('backgroundColor', '#fff');

// Manually clear a change in the jQuery safetynet plugin
  $.safetynet.clearChange('abEntry' + id);
}

$(function() {
  $('#dialogDeleteAddress').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      '<?php echo addslashes(OSCOM::getDef('button_delete')); ?>': function() {
        $(this).dialog('close');

        $('#cEditForm').append('<input type="hidden" id="abDelete' + $(this).data('id') + '" name="deleteAB[]" value="' + $(this).data('id') + '" />');

        $('#abEntry' + $(this).data('id')).css('backgroundColor', '#FFCBC8');
        $('#abEntry' + $(this).data('id') + ' .abActions .default').hide();
        $('#abEntry' + $(this).data('id') + ' .abActions').append('<span class="undo"><a href="#" onclick="undoDeleteAddress(' + $(this).data('id') + '); return false;">' + undo_icon + '</a></span>');

// Manually register a change in the jQuery safetynet plugin
        $.safetynet.raiseChange('abEntry' + $(this).data('id'));
      },
      '<?php echo addslashes(OSCOM::getDef('button_cancel')); ?>': function() {
        $(this).dialog('close');
      }
    }
  });

  $('#dialogDeleteNewAddress').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      '<?php echo addslashes(OSCOM::getDef('button_delete')); ?>': function() {
        $(this).dialog('close');

// Set the original default address
        if ( $('#newAB' + $(this).data('id') + ' .abDefault').length > 0 ) {
          $('#' + ab_default_orig).append(ab_default_marker);
          ab_default = ab_default_orig;

// Manually clear a change in the jQuery safetynet plugin
          $.safetynet.clearChange('abDefault');
        }

        $('#newAB' + $(this).data('id')).remove();
        $('#sectionMenu_addressBook ul').equalResize();

// Manually clear a change in the jQuery safetynet plugin
        $.safetynet.clearChange('newAB' + $(this).data('id'));
      },
      '<?php echo addslashes(OSCOM::getDef('button_cancel')); ?>': function() {
        $(this).dialog('close');
      }
    }
  });

  $('#dialogDeleteDefaultAddress').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      '<?php echo addslashes(OSCOM::getDef('button_ok')); ?>': function() {
        $(this).dialog('close');
      }
    }
  });
});

$(function() {
  $('#sectionMenu input:radio[value=addressBook]').click(function() {
    $('#sectionMenu_addressBook ul').equalResize();
  });
});
</script>
