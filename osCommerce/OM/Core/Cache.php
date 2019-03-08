<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2014 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core;

/**
 * The Cache class handles the caching of dynamically generated data
 */

  class Cache {

/**
 * The cached data
 *
 * @var mixed
 * @access private
 */

    private $_data;

/**
 * The key ID for the cached data
 *
 * @var string
 * @access private
 */

    private $_key;

/**
 * Write the data to a cache file
 *
 * @param mixed $data The data to cache
 * @param string $key The key ID to save the cached data with
 * @access public
 */

    public function write($data, $key = null) {
      if ( !isset($key) ) {
        $key = $this->_key;
      }

      $key = basename($key);

      if ( !static::hasSafeName($key) ) {
        trigger_error('OSCOM_Cache::write(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');

        return false;
      }

      if ( is_writable(OSCOM::BASE_DIRECTORY . 'Work/Cache/') ) {
        return ( file_put_contents(OSCOM::BASE_DIRECTORY . 'Work/Cache/' . $key . '.cache', serialize($data), LOCK_EX) !== false );
      }

      return false;
    }

/**
 * Read data from a cache file if it has not yet expired
 *
 * @param string $key The key ID to read the data from the cached file
 * @param int $expire The amount of minutes the cached data is active for
 * @access public
 * @return boolean
 */

    public function read($key, $expire = null) {
      $key = basename($key);

      if ( !static::hasSafeName($key) ) {
        trigger_error('OSCOM_Cache::read(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');

        return false;
      }

      $this->_key = $key;

      $filename = OSCOM::BASE_DIRECTORY . 'Work/Cache/' . $key . '.cache';

      if ( file_exists($filename) ) {
        $difference = floor((time() - filemtime($filename)) / 60);

        if ( empty($expire) || ( is_numeric($expire) && ($difference < $expire)) ) {
          $this->_data = unserialize(file_get_contents($filename));

          return true;
        }
      }

      return false;
    }

/**
 * Return the cached data
 *
 * @access public
 * @return mixed
 */

    public function getCache() {
      return $this->_data;
    }

/**
 * @since v3.0.3
 */

    public static function hasSafeName($key) {
      return preg_match('/^[a-zA-Z0-9-_]+$/', $key) === 1;
    }

/**
 * Start the buffer to cache its contents
 *
 * @access public
 */

    public function startBuffer() {
      ob_start();
    }

/**
 * Stop the buffer and cache its contents
 *
 * @access public
 */

    public function stopBuffer() {
      $this->_data = ob_get_contents();

      ob_end_clean();

      $this->write($this->_data);
    }

/**
 * Delete cached files by their key ID
 *
 * @param string $key The key ID of the cached files to delete
 * @access public
 */

    public static function clear($key) {
      $key = basename($key);

      if ( !static::hasSafeName($key) ) {
        trigger_error('OSCOM_Cache::clear(): Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_');

        return false;
      }

      if ( is_writable(OSCOM::BASE_DIRECTORY . 'Work/Cache/') ) {
        $key_length = strlen($key);

        $d = dir(OSCOM::BASE_DIRECTORY . 'Work/Cache/');

        while ( ($entry = $d->read()) !== false ) {
          if ( (strlen($entry) >= $key_length) && (substr($entry, 0, $key_length) == $key) ) {
            @unlink(OSCOM::BASE_DIRECTORY . 'Work/Cache/' . $entry);
          }
        }

        $d->close();
      }
    }
  }
?>
