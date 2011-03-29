<?php
/*
  osCommerce Online Merchant $osCommerce-SIG$
  Copyright (c) 2010 osCommerce (http://www.oscommerce.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  namespace osCommerce\OM\Core\Site\Admin;

  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\XML;

  class Language extends \osCommerce\OM\Core\Language {
    public function __construct() {
      parent::__construct();

      header('Content-Type: text/html; charset=' . $this->getCharacterSet());
      setlocale(LC_TIME, explode(',', $this->getLocale()));

      $this->loadIniFile();
      $this->loadIniFile(OSCOM::getSiteApplication() . '.php');
    }

    public function loadIniFile($filename = null, $comment = '#', $language_code = null) {
      if ( is_null($language_code) ) {
        $language_code = $this->_code;
      }

      if ( $this->_languages[$language_code]['parent_id'] > 0 ) {
        $this->loadIniFile($filename, $comment, $this->getCodeFromID($this->_languages[$language_code]['parent_id']));
      }

      if ( is_null($filename) ) {
        if ( file_exists(OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSite() . '/languages/' . $language_code . '.php') ) {
          $contents = file(OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSite() . '/languages/' . $language_code . '.php');
        } else {
          return array();
        }
      } else {
        if ( substr(realpath(OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSite() . '/languages/' . $language_code . '/' . $filename), 0, strlen(realpath(OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSiteApplication() . '/languages/' . $language_code))) != realpath(OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSiteApplication() . '/languages/' . $language_code) ) {
          return array();
        }

        if ( !file_exists(OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSite() . '/languages/' . $language_code . '/' . $filename) ) {
          return array();
        }

        $contents = file(OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSite() . '/languages/' . $language_code . '/' . $filename);
      }

      $ini_array = array();

      foreach ( $contents as $line ) {
        $line = trim($line);

        $firstchar = substr($line, 0, 1);

        if ( !empty($line) && ( $firstchar != $comment) ) {
          $delimiter = strpos($line, '=');

          if ( $delimiter !== false ) {
            $key = trim(substr($line, 0, $delimiter));
            $value = trim(substr($line, $delimiter + 1));

            $ini_array[$key] = $value;
          } elseif ( isset($key) ) {
            $ini_array[$key] .= "\n" . trim($line);
          }
        }
      }

      unset($contents);

      $this->_definitions = array_merge($this->_definitions, $ini_array);
    }

    public function injectDefinitions($file, $language_code = null) {
      if ( is_null($language_code) ) {
        $language_code = $this->_code;
      }

      if ( $this->_languages[$language_code]['parent_id'] > 0 ) {
        $this->injectDefinitions($file, $this->getCodeFromID($this->_languages[$language_code]['parent_id']));
      }

      foreach ($this->extractDefinitions($language_code . '/' . $file) as $def) {
        $this->_definitions[$def['key']] = $def['value'];
      }
    }

    public static function extractDefinitions($xml) {
      $definitions = array();

      if ( file_exists(OSCOM::BASE_DIRECTORY . 'Core/Site/Shop/Languages/' . $xml) ) {
        $definitions = XML::toArray(simplexml_load_file(OSCOM::BASE_DIRECTORY . 'Core/Site/Shop/Languages/' . $xml));

        if ( !isset($definitions['language']) ) { // create root element (simpleXML does not use root element)
          $definitions = array('language' => $definitions);
        }

        if ( !isset($definitions['language']['definitions']['definition'][0]) ) {
          $definitions['language']['definitions']['definition'] = array($definitions['language']['definitions']['definition']);
        }

        $definitions = $definitions['language']['definitions']['definition'];
      }

      return $definitions;
    }

    function getData($id, $key = null) {
      $data = array('id' => $id);

      $result = OSCOM::callDB('Admin\GetLanguage', $data, 'Site');

      if ( empty($key) ) {
        return $result;
      } else {
        return $result[$key];
      }
    }

    function getID($code = null) {
      if ( empty($code) ) {
        return $this->_languages[$this->_code]['id'];
      }

      $data = array('code' => $code);

      $result = OSCOM::callDB('Admin\GetLanguageID', $data, 'Site');

      return $result['languages_id'];
    }

    function getCode($id = null) {
      if ( empty($id) ) {
        return $this->_code;
      }

      return $this->getData($id, 'code');
    }

    function isDefined($key) {
      return isset($this->_definitions[$key]);
    }
  }
?>
