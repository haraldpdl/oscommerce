<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

class Cache
{
    protected const SAFE_KEY_NAME_REGEX = 'a-zA-Z0-9-_';

    protected $_data;
    protected $_key;

    public function write($data, string $key = null): bool
    {
        if (!isset($key)) {
            $key = $this->_key;
        }

        $key = basename($key);

        if (!static::hasSafeName($key)) {
            trigger_error('OSCOM\\Cache::write(): Invalid key name ("' . $key . '"). Valid characters are ' . static::SAFE_KEY_NAME_REGEX);

            return false;
        }

        if (is_writable(OSCOM::BASE_DIRECTORY . 'Work/Cache/')) {
            return file_put_contents(OSCOM::BASE_DIRECTORY . 'Work/Cache/' . $key . '.cache', serialize($data), LOCK_EX) !== false;
        }

        return false;
    }

    public function read(string $key, int $expire = null): bool
    {
        $key = basename($key);

        if (!static::hasSafeName($key)) {
            trigger_error('OSCOM\\Cache::read(): Invalid key name ("' . $key . '"). Valid characters are ' . static::SAFE_KEY_NAME_REGEX);

            return false;
        }

        $this->_key = $key;

        $filename = OSCOM::BASE_DIRECTORY . 'Work/Cache/' . $key . '.cache';

        if (is_file($filename)) {
            $difference = floor((time() - filemtime($filename)) / 60);

            if (empty($expire) || (Is::Integer($expire) && ($difference < $expire))) {
                $contents = file_get_contents($filename);

                if ($contents !== false) {
                    $this->_data = unserialize($contents);

                    return true;
                }
            }
        }

        return false;
    }

    public function getCache()
    {
        return $this->_data;
    }

    public static function hasSafeName(string $key): bool
    {
        return preg_match('/^[' . static::SAFE_KEY_NAME_REGEX . ']+$/', $key) === 1;
    }

    public function startBuffer()
    {
        ob_start();
    }

    public function stopBuffer()
    {
        $this->_data = ob_get_contents();

        ob_end_clean();

        $this->write($this->_data);
    }

    public static function clear(string $key)
    {
        $key = basename($key);

        if (!static::hasSafeName($key)) {
            trigger_error('OSCOM\\Cache::clear(): Invalid key name ("' . $key . '"). Valid characters are ' . static::SAFE_KEY_NAME_REGEX);

            return false;
        }

        if (is_writable(OSCOM::BASE_DIRECTORY . 'Work/Cache/')) {
            $key_length = strlen($key);

            $DLcache = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Work/Cache');
            $DLcache->setIncludeDirectories(false);

            foreach ($DLcache->getFiles() as $file) {
                if ((strlen($file['name']) >= $key_length) && (substr($file['name'], 0, $key_length) == $key)) {
                    unlink(OSCOM::BASE_DIRECTORY . 'Work/Cache/' . $file['name']);
                }
            }
        }
    }
}
