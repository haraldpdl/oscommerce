<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

  namespace osCommerce\OM\Core;

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;

/**
 * The Template class defines or adds elements to the page output such as the page title, page content, and javascript blocks
 */

  class Template {

/**
 * Holds the template name value
 *
 * @var string
 * @access protected
 */

    protected $_template;

/**
 * Holds the template ID value
 *
 * @var int
 * @access protected
 */

    protected $_template_id;

/**
 * Holds the title of the page module
 *
 * @var string
 * @access protected
 */

    protected $_module;

/**
 * Holds the Application object instance
 *
 * @var ApplicationAbstract
 * @access protected
 */

    protected $_application;

/**
 * Holds the group name of the page
 *
 * @var string
 * @access protected
 */

    protected $_group;

/**
 * Holds the image of the page
 *
 * @var string
 * @access protected
 */

    protected $_page_image;

/**
 * Holds the meta tags of the page
 *
 * @var array
 * @access protected
 */

    protected $_page_tags = array('generator' => array('osCommerce Online Merchant'));

/**
 * Holds javascript filenames to be included in the page
 *
 * The javascript files must be plain javascript files without any PHP logic, and are linked to from the page
 *
 * @var array
 * @access protected
 */

    protected $_javascript_filenames = array('public/sites/Shop/javascript/general.js');

/**
 * Holds javascript PHP filenames to be included in the page
 *
 * The javascript PHP filenames can consist of PHP logic to produce valid javascript syntax, and is embedded in the page
 *
 * @var array
 * @access protected
 */

    protected $_javascript_php_filenames = array();

/**
 * Holds blocks of javascript syntax to embedd into the page
 *
 * Each block must contain its relevant <script> and </script> tags
 *
 * @var array
 * @access protected
 */

    protected $_javascript_blocks = array();

/**
 * Defines if the requested page has a header
 *
 * @var boolean
 * @access protected
 */

    protected $_has_header = true;

/**
 * Defines if the requested page has a footer
 *
 * @var boolean
 * @access protected
 */

    protected $_has_footer = true;

/**
 * Defines if the requested page has box modules
 *
 * @var boolean
 * @access protected
 */

    protected $_has_box_modules = true;

/**
 * Defines if the requested page has content modules
 *
 * @var boolean
 * @access protected
 */

    protected $_has_content_modules = true;

/**
 * Defines if the requested page should display any debug messages
 *
 * @var boolean
 * @access protected
 */

    protected $_show_debug_messages = true;

/**
 * The base URL
 *
 * @var string
 * @access protected
 * @since HPDL
 */

    protected $_base_url;

/**
 * Values to make available to pages
 *
 * @var array
 * @access protected
 * @since HPDL
 */

    protected $_values = array();

    public function setApplication(ApplicationAbstract $application) {
      $this->_application = $application;
    }

/**
 * Returns the template ID
 *
 * @access public
 * @return int
 */

    function getID() {
      if (isset($this->_template) === false) {
        $this->set();
      }

      return $this->_template_id;
    }

/**
 * Returns the template name
 *
 * @access public
 * @return string
 */

    function getCode($id = null) {
      if (isset($this->_template) === false) {
        $this->set();
      }

      if (is_numeric($id)) {
        foreach ($this->getTemplates() as $template) {
          if ($template['id'] == $id) {
            return $template['code'];
          }
        }
      } else {
        return $this->_template;
      }
    }

/**
 * Returns the page module name
 *
 * @access public
 * @return string
 */

    function getModule() {
      return OSCOM::getSiteApplication();
    }

/**
 * Returns the page group name
 *
 * @access public
 * @return string
 */

    function getGroup() {
      return $this->_group;
    }

/**
 * Returns the title of the page
 *
 * @access public
 * @return string
 */

    function getPageTitle() {
      return HTML::outputProtected($this->_application->getPageTitle());
    }

/**
 * Returns the tags of the page separated by a comma
 *
 * @access public
 * @return string
 */

    function getPageTags() {
      $tag_string = '';

      foreach ($this->_page_tags as $key => $values) {
        $tag_string .= '<meta name="' . $key . '" content="' . implode(', ', $values) . '" />' . "\n";
      }

      return $tag_string . "\n";
    }

/**
 * Return the box modules assigned to the page
 *
 * @param string $group The group name of box modules to include that the template has provided
 * @return array
 */

    function getBoxModules($group) {
      if ( !isset($this->osC_Modules_Boxes) ) {
        $this->osC_Modules_Boxes = new Modules('Box');
      }

      return $this->osC_Modules_Boxes->getGroup($group);
    }

/**
 * Return the content modules assigned to the page
 *
 * @param string $group The group name of content modules to include that the template has provided
 * @return array
 */

    function getContentModules($group) {
      if ( !isset($this->osC_Modules_Content) ) {
        $this->osC_Modules_Content = new Modules('Content');
      }

      return $this->osC_Modules_Content->getGroup($group);
    }

    public function getTemplateFile($file = null) {
      if ( empty($file) ) {
        $file = $this->getCode() . '.php';
      } else {
        $file = $this->getCode() . '/' . $file;
      }

      $file_location = OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSite() . '/templates/' . $file;

      if ( file_exists(OSCOM::BASE_DIRECTORY . 'Custom/Site/' . OSCOM::getSite() . '/templates/' . $file) ) {
        $file_location = OSCOM::BASE_DIRECTORY . 'Custom/Site/' . OSCOM::getSite() . '/templates/' . $file;
      }

      return $file_location;
    }

/**
 * Returns the image of the page
 *
 * @access public
 * @return string
 */

    function getPageImage() {
      return $this->_page_image;
    }

    public function getPageContentsFile() {
      $file_location = OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSite() . '/Application/' . OSCOM::getSiteApplication() . '/pages/' . $this->getPageContentsFilename();

      if ( file_exists(OSCOM::BASE_DIRECTORY . 'Custom/Site/' . OSCOM::getSite() . '/Application/' . OSCOM::getSiteApplication() . '/pages/' . $this->getPageContentsFilename()) ) {
        $file_location = OSCOM::BASE_DIRECTORY . 'Custom/Site/' . OSCOM::getSite() . '/Application/' . OSCOM::getSiteApplication() . '/pages/' . $this->getPageContentsFilename();
      }

      return $file_location;
    }

/**
 * Returns the content filename of the page
 *
 * @access public
 * @return string
 */

    function getPageContentsFilename() {
      return $this->_application->getPageContent();
    }

/**
 * Returns the javascript to link from or embedd to on the page
 *
 * @access public
 * @return string
 */

    function getJavascript() {
      if (!empty($this->_javascript_filenames)) {
        echo $this->_getJavascriptFilenames();
      }

      if (!empty($this->_javascript_php_filenames)) {
        $this->_getJavascriptPhpFilenames();
      }

      if (!empty($this->_javascript_blocks)) {
        echo $this->_getJavascriptBlocks();
      }
    }

/**
 * Return all templates in an array
 *
 * @access public
 * @return array
 */

    public static function getTemplates() {
      return OSCOM::callDB('GetTemplates', null, 'Core');
    }

/**
 * Checks to see if the page has a title set
 *
 * @access public
 * @return boolean
 */

    function hasPageTitle() {
      return strlen($this->_application->getPageTitle()) > 0;
    }

/**
 * Checks to see if the page has a meta tag set
 *
 * @access public
 * @return boolean
 */

    function hasPageTags() {
      return !empty($this->_page_tags);
    }

/**
 * Checks to see if the page has javascript to link to or embedd from
 *
 * @access public
 * @return boolean
 */

    function hasJavascript() {
      return (!empty($this->_javascript_filenames) || !empty($this->_javascript_php_filenames) || !empty($this->_javascript_blocks));
    }

/**
 * Checks to see if the page has a footer defined
 *
 * @access public
 * @return boolean
 */

    function hasPageFooter() {
      return $this->_has_footer;
    }

/**
 * Checks to see if the page has a header defined
 *
 * @access public
 * @return boolean
 */

    function hasPageHeader() {
      return $this->_has_header;
    }

/**
 * Checks to see if the page has content modules defined
 *
 * @access public
 * @return boolean
 */

    function hasPageContentModules() {
      return $this->_has_content_modules;
    }

/**
 * Checks to see if the page has box modules defined
 *
 * @access public
 * @return boolean
 */

    function hasPageBoxModules() {
      return $this->_has_box_modules;
    }

/**
 * Checks to see if the page show display debug messages
 *
 * @access public
 * @return boolean
 */

    function showDebugMessages() {
      return $this->_show_debug_messages;
    }

/**
 * Sets the template to use
 *
 * @param string $code The code of the template to use
 * @access public
 */

    function set($code = null) {
      if ( !isset($_SESSION['template']) || !empty($code) || (isset($_GET['template']) && !empty($_GET['template'])) ) {
        if ( !empty( $code ) ) {
          $set_template = $code;
        } else {
          $set_template = (isset($_GET['template']) && !empty($_GET['template'])) ? $_GET['template'] : DEFAULT_TEMPLATE;
        }

        $data = array();
        $data_default = array();

        foreach ($this->getTemplates() as $template) {
          if ($template['code'] == DEFAULT_TEMPLATE) {
            $data_default = array('id' => $template['id'], 'code' => $template['code']);
          } elseif ($template['code'] == $set_template) {
            $data = array('id' => $template['id'], 'code' => $template['code']);
          }
        }

        if (empty($data)) {
          $data =& $data_default;
        }

        $_SESSION['template'] =& $data;
      }

      $this->_template_id =& $_SESSION['template']['id'];
      $this->_template =& $_SESSION['template']['code'];
    }

/**
 * Sets the title of the page
 *
 * @param string $title The title of the page to set to
 * @access public
 */

    function setPageTitle($title) {
      $this->_application->setPageTitle($title);
    }

/**
 * Sets the image of the page
 *
 * @param string $image The image of the page to set to
 * @access public
 */

    function setPageImage($image) {
      $this->_page_image = $image;
    }

/**
 * Sets the content of the page
 *
 * @param string $filename The content filename to include on the page
 * @access public
 */

    function setPageContentsFilename($filename) {
      $this->_application->setPageContent($filename);
    }

/**
 * Adds a tag to the meta keywords array
 *
 * @param string $key The keyword for the meta tag
 * @param string $value The value for the meta tag using the key
 * @access public
 */

    function addPageTags($key, $value) {
      $this->_page_tags[$key][] = $value;
    }

/**
 * Adds a javascript file to link to
 *
 * @param string $filename The javascript filename to link to
 * @access public
 */

    function addJavascriptFilename($filename) {
      $this->_javascript_filenames[] = $filename;
    }

/**
 * Adds a PHP based javascript file to embedd on the page
 *
 * @param string $filename The PHP based javascript filename to embedd
 * @access public
 */

    function addJavascriptPhpFilename($filename) {
      $this->_javascript_php_filenames[] = $filename;
    }

/**
 * Adds javascript logic to the page
 *
 * @param string $javascript The javascript block to add on the page
 * @access public
 */

    function addJavascriptBlock($javascript) {
      $this->_javascript_blocks[] = $javascript;
    }

/**
 * Returns the javascript filenames to link to on the page
 *
 * @access private
 * @return string
 */

    function _getJavascriptFilenames() {
      $js_files = '';

      foreach ($this->_javascript_filenames as $filenames) {
        $js_files .= '<script language="javascript" type="text/javascript" src="' . $filenames . '"></script>' . "\n";
      }

      return $js_files;
    }

/**
 * Returns the PHP javascript files to embedd on the page
 *
 * @access private
 */

    function _getJavascriptPhpFilenames() {
      foreach ($this->_javascript_php_filenames as $filenames) {
        include($filenames);
      }
    }

/**
 * Returns javascript blocks to add to the page
 *
 * @access private
 * @return string
 */

    function _getJavascriptBlocks() {
      return implode("\n", $this->_javascript_blocks);
    }

    public function setHasHeader($bool) {
      $this->_has_header = $bool;
    }

    public function setHasFooter($bool) {
      $this->_has_footer = $bool;
    }

    public function setHasBoxModules($bool) {
      $this->_has_box_modules = $bool;
    }

    public function setHasContentModules($bool) {
      $this->_has_content_modules = $bool;
    }

    public function setShowDebugMessages($bool) {
      $this->_show_debug_messages = $bool;
    }

/**
 * @since HPDL
 */

    public function getContent($file = null) {
      if ( !isset($file) ) {
        $file = $this->getTemplateFile();
      }

// use only file_get_contents() when content pages no longer contain PHP; HPDL
      if ( substr($file, strrpos($file, '.')+1) == 'html' ) {
        $content = file_get_contents($file);
      } else {
// The following is only needed until content pages no longer contain PHP; HPDL
        $rick_astley = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_SESSION', '_FILES', '_SERVER');
        $never_gonna_give_you_up = array();

        foreach ( array_keys($GLOBALS) as $k ) {
          if ( !in_array($k, $rick_astley) ) {
            $never_gonna_give_you_up[$k] = $GLOBALS[$k];
          }
        }

        extract($never_gonna_give_you_up);

        ob_start();

        include($file);

        $content = ob_get_clean();
      }

      return $this->parseContent($content);
    }

/**
 * @since HPDL
 */

    public function parseContent($content, $whitelist = null) {
      static $loaded_tags = array();

      $pattern = '/(?<![\$\{])\{([[:alpha:]][[:alnum:]]*)\b( .*?)?\}(.*?)?(?<![\$\{])\{\1\}/s';

      $le = function ($matches) use (&$le, &$pattern, $whitelist) {
        $string = $matches[3];

        if ( preg_match($pattern, $string) > 0 ) {
          $string = preg_replace_callback($pattern, $le, $string);
        }

        if ( isset($whitelist) && is_array($whitelist) && !in_array($matches[1], $whitelist) ) {
          return $string;
        }

        $tag_class = null;

        if ( class_exists('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Module\\Template\\Tag\\' . $matches[1]) ) {
          $tag_class = 'osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Module\\Template\\Tag\\' . $matches[1];
        } elseif ( class_exists('osCommerce\\OM\\Core\\Template\\Tag\\' . $matches[1]) ) {
          $tag_class = 'osCommerce\\OM\\Core\\Template\\Tag\\' . $matches[1];
        }

        if ( isset($tag_class) ) {
          if ( (isset($loaded_tags[$matches[1]]) && ($loaded_tags[$matches[1]] === true) ) || is_subclass_of($tag_class, 'osCommerce\\OM\\Core\\Template\\TagAbstract') ) {
            if ( !isset($loaded_tags[$matches[1]]) ) {
              $loaded_tags[$matches[1]] = true;
            }

            $output = call_user_func(array($tag_class, 'execute'), $string, $matches[2]);

            if ( call_user_func(array($tag_class, 'parseResult')) === true ) {
              $output = preg_replace_callback($pattern, $le, $output);
            }

            return $output;
          } else {
            if ( !isset($loaded_tags[$matches[1]]) ) {
              $loaded_tags[$matches[1]] = false;
            }

            trigger_error('Template Tag {' . $matches[1] . '} module does not implement TagInterface');
          }
        } else {
          trigger_error('Template Tag {' . $matches[1] . '} module does not exist');

          return $matches[0];
        }
      };

      return preg_replace_callback($pattern, $le, $content);
    }

/**
 * @since HPDL
 */

    public function getValue($key) {
      if ( !$this->valueExists($key) ) {
        trigger_error('OSCOM_Template::getValue - ' . $key . ' is not set');

        return false;
      }

      return $this->_values[$key];
    }

/**
 * @since HPDL
 */

    public function setValue($key, $value, $force = false) {
      if ( $this->valueExists($key, false) && ($force !== true) ) {
        trigger_error('OSCOM_Template::setValue - ' . $key . ' already set and is not forced to be replaced');

        return false;
      }

      $this->_values[$key] = $value;
    }

/**
 * @since HPDL
 */

    public function valueExists($key, $autoload = true) {
      if (($autoload === true) && array_key_exists($key, $this->_values) === false) {
        if (class_exists('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Module\\Template\\Value\\' . $key . '\\Controller') && is_subclass_of('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'osCommerce\\OM\\Core\\Template\\ValueAbstract')) {
          call_user_func(array('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'initialize'));
        } elseif (class_exists('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Module\\Template\\Value\\' . $key . '\\Controller') && is_subclass_of('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'osCommerce\\OM\\Core\\Template\\ValueAbstract')) {
          call_user_func(array('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Module\\Template\\Value\\' . $key . '\\Controller', 'initialize'));
        }
      }

      return array_key_exists($key, $this->_values);
    }

/**
 * @since HPDL
 */

    public function getBaseUrl() {
      if ( !isset($this->_base_url) ) {
        if ( (OSCOM::getRequestType() == 'SSL') && (OSCOM::getConfig('enable_ssl') == 'true') ) {
          $this->_base_url = OSCOM::getConfig('https_server') . OSCOM::getConfig('dir_ws_https_server');
        } else {
          $this->_base_url = OSCOM::getConfig('http_server') . OSCOM::getConfig('dir_ws_http_server');
        }
      }

      return $this->_base_url;
    }
  }
?>
