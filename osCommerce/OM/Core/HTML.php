<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core;

  use osCommerce\OM\Core\DateTime;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

  class HTML {

/**
 * Parse a user submited value
 *
 * @param string $string The string to parse and output
 * @param array $translate An array containing the characters to parse
 * @return string
 * @since v3.0.0
 */

    public static function output($string, $translate = null) {
      if ( !isset($translate) ) {
        $translate = array('"' => '&quot;');
      }

      return strtr(trim($string), $translate);
    }

/**
 * Strictly parse a user submited value
 *
 * @param string $string The string to strictly parse and output
 * @return string
 * @since v3.0.0
 */

    public static function outputProtected($string) {
      return htmlspecialchars(trim($string));
    }

/**
 * Sanitize a user submited value
 *
 * @param string $string The string to sanitize
 * @return string
 * @since v3.0.0
 */

    public static function sanitize($string) {
      $patterns = array ('/ +/', '/[<>]/');
      $replace = array (' ', '_');

      return preg_replace($patterns, $replace, trim($string));
    }

/**
 * Generate a <a href> tag and link to an element
 *
 * @param string $url The url to link to
 * @param string $element The element to link to
 * @param string $parameters Additional parameters for the a href tag
 * @return string
 * @since v3.0.0
 */

    public static function link($url, $element, $parameters = null) {
      return '<a href="' . $url . '"' . (!empty($parameters) ? ' ' . $parameters : '') . '>' . $element . '</a>';
    }

/**
 * Generate an <img> tag
 *
 * @param string $image The image filename to display
 * @param string $title The title of the image button
 * @param int $width The width of the image
 * @param int $height The height of the image
 * @param string $parameters Additional parameters for the image
 * @return string
 * @since v3.0.0
 */

    public static function image($image, $title = null, $width = 0, $height = 0, $parameters = null) {
      if ( !is_numeric($width) ) {
        $width = 0;
      }

      if ( !is_numeric($height) ) {
        $height = 0;
      }

      $image = '<img src="' . static::output($image) . '" border="0" alt="' . static::output($title) . '"';

      if (!empty($title)) {
        $image .= ' title="' . static::output($title) . '"';
      }

      if ($width > 0) {
        $image .= ' width="' . (int)$width . '"';
      }

      if ($height > 0) {
        $image .= ' height="' . (int)$height . '"';
      }

      if (!empty($parameters)) {
        $image .= ' ' . $parameters;
      }

      $image .= ' />';

      return $image;
    }

/**
 * Generate an icon from a template set
 *
 * @param string $image The icon to display
 * @param string $title The title of the icon
 * @param string $group The size group of the icon
 * @param string $parameters The parameters to pass to the image
 * @return string
 * @since v3.0.0
 */

    public static function icon($image, $title = null, $group = null, $parameters = null) {
      if ( is_null($title) ) {
        $title = OSCOM::getDef('icon_' . substr($image, 0, strpos($image, '.')));
      }

      if ( is_null($group) ) {
        $group = '16x16';
      }

      return static::image(OSCOM::getPublicSiteLink('templates/' . Registry::get('Template')->getCode() . '/images/icons/' . (!empty($group) ? $group . '/' : null) . $image), $title, null, null, $parameters);
    }

/**
 * Generate a public url to an icon from a template set
 *
 * @param string $image The icon to display
 * @param string $group The size group of the icon
 * @return string
 * @since v3.0.0
 */

    public static function iconRaw($image, $group = null) {
      if ( is_null($group) ) {
        $group = '16x16';
      }

      return OSCOM::getPublicSiteLink('templates/' . Registry::get('Template')->getCode() . '/images/icons/' . (!empty($group) ? $group . '/' : null) . $image);
    }

/**
 * Generate an image submit tag
 *
 * @param string $image The image filename to display
 * @param string $title The title of the image button
 * @param string $parameters Additional parameters for the image
 * @return string
 * @since v3.0.0
 */

    public static function submitImage($image, $title = null, $parameters = null) {
      $submit = '<input type="image" src="' . static::output($image) . '"';

      if (!empty($title)) {
        $submit .= ' title="' . static::output($title) . '"';
      }

      if (!empty($parameters)) {
        $submit .= ' ' . $parameters;
      }

      $submit .= ' />';

      return $submit;
    }

/**
 * Generate a jQuery UI button
 * 
 * @param array $params types(submit, button, reset), href, newwindow, params, title, icon, iconpos(left, right), priority(primary, secondary)
 * @return string
 * @since v3.0.0
 */

    public static function button($params) {
      static $button_counter = 1;

      $types = array('submit', 'button', 'reset');

      if ( !isset($params['type']) ) {
        $params['type'] = 'submit';
      }

      if ( !in_array($params['type'], $types) ) {
        $params['type'] = 'submit';
      }

      if ( ($params['type'] == 'submit') && isset($params['href']) ) {
        $params['type'] = 'button';
      }

      $button = '<button id="button' . $button_counter . '" type="' . static::output($params['type']) . '"';

      if ( isset($params['href']) ) {
        if ( isset($params['newwindow']) ) {
          $button .= ' onclick="window.open(\'' . $params['href'] . '\');"';
        } else {
          $button .= ' onclick="window.location.href=\'' . $params['href'] . '\';"';
        }
      }

      if ( isset($params['params']) ) {
        $button .= ' ' . $params['params'];
      }

      $button .= '>' . $params['title'] . '</button><script type="text/javascript">$("#button' . $button_counter . '").button(';

      if ( isset($params['icon']) ) {
        if ( !isset($params['iconpos']) ) {
          $params['iconpos'] = 'left';
        }

        if ( $params['iconpos'] == 'left' ) {
          $button .= '{icons:{primary:"ui-icon-' . $params['icon'] . '"}}';
        } else {
          $button .= '{icons:{secondary:"ui-icon-' . $params['icon'] . '"}}';
        }
      }

      $button .= ')';

      if ( isset($params['priority']) ) {
        $button .= '.addClass("ui-priority-' . $params['priority'] . '")';
      }

      $button .= ';</script>';

      $button_counter++;

      return $button;
    }

/**
 * Generate a form input field (text/password)
 *
 * @param string $name The name and ID of the input field
 * @param string $value The default value for the input field
 * @param string $parameters Additional parameters for the input field
 * @param boolean $override Override the default value with the value found in the GET or POST scope
 * @param string $type The type of input field to use (text/password/file)
 * @return string
 * @since v3.0.0
 */

    public static function inputField($name, $value = null, $parameters = null, $override = true, $type = 'text') {
      if ( !is_bool($override) ) {
        $override = true;
      }

      if ( $override === true ) {
        if ( strpos($name, '[') !== false ) {
          $name_string = substr($name, 0, strpos($name, '['));
          $name_key = substr($name, strpos($name, '[') + 1, strlen($name) - (strpos($name, '[') + 2));

          if ( isset($_GET[$name_string][$name_key]) ) {
            $value = $_GET[$name_string][$name_key];
          } elseif ( isset($_POST[$name_string][$name_key]) ) {
            $value = $_POST[$name_string][$name_key];
          }
        } else {
          if ( isset($_GET[$name]) ) {
            $value = $_GET[$name];
          } elseif ( isset($_POST[$name]) ) {
            $value = $_POST[$name];
          }
        }
      }

      if ( !in_array($type, array('text', 'password', 'file')) ) {
        $type = 'text';
      }

      $field = '<input type="' . static::output($type) . '" name="' . static::output($name) . '"';

      if ( strpos($parameters, 'id=') === false ) {
        $field .= ' id="' . static::output($name) . '"';
      }

      if ( !empty($value) ) {
        $field .= ' value="' . static::output($value) . '"';
      }

      if ( !empty($parameters) ) {
        $field .= ' ' . $parameters;
      }

      $field .= ' />';

      return $field;
    }

/**
 * Generate a form password field
 *
 * @param string $name The name and ID of the password field
 * @param string $parameters Additional parameters for the password field
 * @return string
 * @since v3.0.0
 */

    public static function passwordField($name, $parameters = null) {
      return static::inputField($name, null, $parameters, false, 'password');
    }

/**
 * Generate a form textarea field
 *
 * @param string $name The name and ID of the textarea field
 * @param string $value The default value for the textarea field
 * @param int $width The width of the textarea field
 * @param int $height The height of the textarea field
 * @param string $parameters Additional parameters for the textarea field
 * @param boolean $override Override the default value with the value found in the GET or POST scope
 * @return string
 * @since v3.0.0
 */

  public static function textareaField($name, $value = null, $width = 60, $height = 5, $parameters = null, $override = true) {
    if ( !is_bool($override) ) {
      $override = true;
    }

    if ( $override === true ) {
      if ( isset($_GET[$name]) ) {
        $value = $_GET[$name];
      } elseif ( isset($_POST[$name]) ) {
        $value = $_POST[$name];
      }
    }

    if ( !is_numeric($width) ) {
      $width = 60;
    }

    if ( !is_numeric($height) ) {
      $width = 5;
    }

    $field = '<textarea name="' . static::output($name) . '" cols="' . (int)$width . '" rows="' . (int)$height . '"';

    if ( strpos($parameters, 'id=') === false ) {
      $field .= ' id="' . static::output($name) . '"';
    }

    if ( !empty($parameters) ) {
      $field .= ' ' . $parameters;
    }

    $field .= '>' . static::outputProtected($value) . '</textarea>';

    return $field;
  }

/**
 * Generate a form select menu field
 *
 * @param string $name The name of the pull down menu field
 * @param array $values Defined values for the pull down menu field [ id, text, group, params (since v3.0.2) ]
 * @param string $default The default value for the pull down menu field
 * @param string $parameters Additional parameters for the pull down menu field
 * @return string
 * @since v3.0.0
 */

    public static function selectMenu($name, $values, $default = null, $parameters = null) {
      $group = false;

      if ( isset($_GET[$name]) ) {
        $default = $_GET[$name];
      } elseif ( isset($_POST[$name]) ) {
        $default = $_POST[$name];
      }

      $field = '<select name="' . static::output($name) . '"';

      if ( strpos($parameters, 'id=') === false ) {
        $field .= ' id="' . static::output($name) . '"';
      }

      if ( !empty($parameters) ) {
        $field .= ' ' . $parameters;
      }

      $field .= '>';

      for ( $i=0, $n=count($values); $i<$n; $i++ ) {
        if ( isset($values[$i]['group']) ) {
          if ( $group != $values[$i]['group'] ) {
            $group = $values[$i]['group'];

            $field .= '<optgroup label="' . static::output($values[$i]['group']) . '">';
          }
        }

        $field .= '<option value="' . static::output($values[$i]['id']) . '"';

        if ( isset($default) && ((!is_array($default) && ((string)$default == (string)$values[$i]['id'])) || (is_array($default) && in_array($values[$i]['id'], $default))) ) {
          $field .= ' selected="selected"';
        }

        if ( isset($values[$i]['params']) ) {
          $field .= ' ' . $values[$i]['params'];
        }

        $field .= '>' . static::output($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';

        if ( ($group !== false) && (($group != $values[$i]['group']) || !isset($values[$i+1])) ) {
          $group = false;

          $field .= '</optgroup>';
        }
      }

      $field .= '</select>';

      return $field;
    }

/**
 * Generate a form selection field (checkbox/radio)
 *
 * @param string $name The name and indexed ID of the selection field
 * @param string $type The type of the selection field (checkbox/radio)
 * @param mixed $values The value of, or an array of values for, the selection field
 * @param string $default The default value for the selection field
 * @param string $parameters Additional parameters for the selection field
 * @param string $separator The separator to use between multiple options for the selection field
 * @return string
 * @since v3.0.0
 */

    protected static function selectionField($name, $type, $values, $default = null, $parameters = null, $separator = '&nbsp;&nbsp;') {
      if ( !is_array($values) ) {
        $values = array($values);
      }

      if ( strpos($name, '[') !== false ) {
        $name_string = substr($name, 0, strpos($name, '['));

        if ( isset($_GET[$name_string]) ) {
          $default = $_GET[$name_string];
        } elseif ( isset($_POST[$name_string]) ) {
          $default = $_POST[$name_string];
        }
      } else {
        if ( isset($_GET[$name]) ) {
          $default = $_GET[$name];
        } elseif ( isset($_POST[$name]) ) {
          $default = $_POST[$name];
        }
      }

      $field = '';

      $counter = 0;

      foreach ( $values as $key => $value ) {
        $counter++;

        if ( is_array($value) ) {
          $selection_value = $value['id'];
          $selection_text = $value['text'];
        } else {
          $selection_value = $value;
          $selection_text = '';
        }

        if ( empty($selection_value) ) {
          $selection_value = 'on';
        }

        $field .= '<input type="' . static::output($type) . '" name="' . static::output($name) . '"';

        if ( strpos($parameters, 'id=') === false ) {
          $field .= ' id="' . static::output($name) . (count($values) > 1 ? '_' . $counter : '') . '"';
        } elseif ( count($values) > 1 ) {
          $offset = strpos($parameters, 'id="');
          $field .= ' id="' . static::output(substr($parameters, $offset+4, strpos($parameters, '"', $offset+4)-($offset+4))) . '_' . $counter . '"';
        }

        $field .= ' value="' . static::output($selection_value) . '"';

        if ( isset($default) && (($default === true) || (!is_array($default) && ((string)$default == (string)$selection_value)) || (is_array($default) && in_array($selection_value, $default))) ) {
          $field .= ' checked="checked"';
        }

        if ( !empty($parameters) ) {
          $field .= ' ' . $parameters;
        }

        $field .= ' />';

        if ( !empty($selection_text) ) {
          $field .= '<label for="' . static::output($name) . (count($values) > 1 ? '_' . $counter : '') . '" class="fieldLabel">' . $selection_text . '</label>';
        }

        $field .= $separator;
      }

      if ( !empty($field) ) {
        $field = substr($field, 0, strlen($field)-strlen($separator));
      }

      return $field;
    }

/**
 * Generate a form checkbox field
 *
 * @param string $name The name and indexed ID of the checkbox field
 * @param mixed $values The value of, or an array of values for, the checkbox field
 * @param string $default The default value for the checkbox field
 * @param string $parameters Additional parameters for the checkbox field
 * @param string $separator The separator to use between multiple options for the checkbox field
 * @return string
 * @since v3.0.0
 */

    public static function checkboxField($name, $values = null, $default = null, $parameters = null, $separator = '&nbsp;&nbsp;') {
      return static::selectionField($name, 'checkbox', $values, $default, $parameters, $separator);
    }

/**
 * Generate a form radio field
 *
 * @param string $name The name and indexed ID of the radio field
 * @param mixed $values The value of, or an array of values for, the radio field
 * @param string $default The default value for the radio field
 * @param string $parameters Additional parameters for the radio field
 * @param string $separator The separator to use between multiple options for the radio field
 * @return string
 * @since v3.0.0
 */

    public static function radioField($name, $values, $default = null, $parameters = null, $separator = '&nbsp;&nbsp;') {
      return static::selectionField($name, 'radio', $values, $default, $parameters, $separator);
    }

/**
 * Generate a form hidden field
 *
 * @param string $name The name of the hidden field
 * @param string $value The value for the hidden field
 * @param string $parameters Additional parameters for the hidden field
 * @return string
 * @since v3.0.0
 */

    public static function hiddenField($name, $value = null, $parameters = null) {
      $field = '<input type="hidden" name="' . static::output($name) . '"';

      if ( !empty($value) ) {
        $field .= ' value="' . static::output($value) . '"';
      }

      if ( !empty($parameters) ) {
        $field .= ' ' . $parameters;
      }

      $field .= ' />';

      return $field;
    }

/**
 * Generate a form hidden field containing the session name and ID if SID is not empty
 *
 * @return string
 * @since v3.0.0
 */

    public static function hiddenSessionIDField() {
      $OSCOM_Session = Registry::get('Session');

      if ( $OSCOM_Session->hasStarted() && (strlen(SID) > 0) ) {
        return static::hiddenField($OSCOM_Session->getName(), $OSCOM_Session->getID());
      }
    }

/**
 * Generate a form file upload field
 *
 * @param string $name The name and ID of the file upload field
 * @return string
 * @since v3.0.2
 */

  public static function fileField($name) {
    return static::inputField($name, null, null, false, 'file');
  }

/**
 * Generate a label for form field elements
 *
 * @param string $text The text to use as the form field label
 * @param string $for The ID of the form field element to assign the label to
 * @param string $access_key The access key to use for the form field element
 * @param bool $required A flag to show if the form field element requires input or not
 * @return string
 * @since v3.0.0
 */

    public static function label($text, $for, $access_key = null, $required = false) {
      if ( !is_bool($required) ) {
        $required = false;
      }

      return '<label for="' . static::output($for) . '"' . (!empty($access_key) ? ' accesskey="' . static::output($access_key) . '"' : '') . '>' . static::output($text) . ($required === true ? '<em>*</em>' : '') . '</label>';
    }

/**
 * Generate a form pull down menu for a date selection
 *
 * @param string $name The base name of the date pull down menu fields
 * @param array $value An array containing the year, month, and date values for the default date (year, month, date)
 * @param boolean $default_today Default to todays date if no default value is used
 * @param boolean $show_days Show the days in a pull down menu
 * @param boolean $use_month_names Show the month names in the month pull down menu
 * @param int $year_range_start The start of the years range to use for the year pull down menu
 * @param int $year_range_end The end of the years range to use for the year pull down menu
 * @return string
 * @since v3.0.0
 */

    public static function dateSelectMenu($name, $value = null, $default_today = true, $show_days = true, $use_month_names = true, $year_range_start = 0, $year_range_end = 1) {
      $year = date('Y');

      if ( !is_bool($default_today) ) {
        $default_today = true;
      }

      if ( !is_bool($show_days) ) {
        $show_days = true;
      }

      if ( !is_bool($use_month_names) ) {
        $use_month_names = true;
      }

      if ( !is_numeric($year_range_start) ) {
        $year_range_start = 0;
      }

      if ( !is_numeric($year_range_end) ) {
        $year_range_end = 1;
      }

      if ( !is_array($value) ) {
        $value = array();
      }

      if ( !isset($value['year']) || !is_numeric($value['year']) || ($value['year'] < ($year - $year_range_start)) || ($value['year'] > ($year + $year_range_end)) ) {
        if ( $default_today === true ) {
          $value['year'] = $year;
        } else {
          $value['year'] = $year - $year_range_start;
        }
      }

      if ( !isset($value['month']) || !is_numeric($value['month']) || ($value['month'] < 1) || ($value['month'] > 12) ) {
        if ( $default_today === true ) {
          $value['month'] = date('n');
        } else {
          $value['month'] = 1;
        }
      }

      if ( !isset($value['date']) || !is_numeric($value['date']) || ($value['date'] < 1) || ($value['date'] > 31) ) {
        if ( $default_today === true ) {
          $value['date'] = date('j');
        } else {
          $value['date'] = 1;
        }
      }

      $params = '';

      $days_select_string = '';

      if ( $show_days === true ) {
        $params = 'onchange="updateDatePullDownMenu(this.form, \'' . $name . '\');"';

        $days_in_month = ($default_today === true) ? date('t') : 31;

        $days_array = array();
        for ( $i=1; $i<=$days_in_month; $i++ ) {
          $days_array[] = array('id' => $i,
                                'text' => $i);
        }

        $days_select_string = static::selectMenu($name . '_days', $days_array, $value['date']);
      }

      $months_array = array();
      for ( $i=1; $i<=12; $i++ ) {
        $months_array[] = array('id' => $i,
                                'text' => (($use_month_names === true) ? strftime('%B', mktime(0, 0, 0, $i, 1)) : $i));
      }

      $months_select_string = static::selectMenu($name . '_months', $months_array, $value['month'], $params);

      $years_array = array();
      for ( $i = ($year - $year_range_start); $i <= ($year + $year_range_end); $i++ ) {
        $years_array[] = array('id' => $i,
                               'text' => $i);
      }

      $years_select_string = static::selectMenu($name . '_years', $years_array, $value['year'], $params);

      return $days_select_string . $months_select_string . $years_select_string;
    }

/**
 * Generate a time zone selection menu
 * 
 * @param $name string The name of the selection field
 * @param $default The default value
 * @return string
 * @since v3.0.1
 */

    public static function timeZoneSelectMenu($name, $default = null) {
      if ( !isset($default) ) {
        $default = date_default_timezone_get();
      }

      $result = array();

      foreach ( DateTime::getTimeZones() as $zone => $zones_array ) {
        foreach ( $zones_array as $key => $value ) {
          $result[] = array('id' => $key,
                        'text' => $value,
                        'group' => $zone);
        }
      }

      return HTML::selectMenu($name, $result, $default);
    }
  }
?>
