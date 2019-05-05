<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM;

use osCommerce\OM\Core\{
    DateTime,
    ErrorHandler,
    OSCOM
};

error_reporting(E_ALL);

define('OSCOM\\BASE_DIRECTORY', __DIR__ . DIRECTORY_SEPARATOR);

mb_internal_encoding('UTF-8');

spl_autoload_register(function($class) {
    $prefix = 'osCommerce\\OM\\';

    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) { // try and autoload external classes
        $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        $file = \OSCOM\BASE_DIRECTORY . 'External' . DIRECTORY_SEPARATOR . $class_path . '.php';

        if (is_file($file)) {
            require($file);

            return true;
        }

        $site_dirs = [
          'Core',
          'Custom'
        ];

        foreach ($site_dirs as $site_dir) {
            $dir = new \DirectoryIterator(\OSCOM\BASE_DIRECTORY . $site_dir . DIRECTORY_SEPARATOR . 'Site');

            foreach ($dir as $f) {
                if (!$f->isDot() && $f->isDir()) {
                    $file = $f->getPath() . DIRECTORY_SEPARATOR . $f->getFilename() . DIRECTORY_SEPARATOR . 'External' . DIRECTORY_SEPARATOR . $class_path . '.php';

                    if (is_file($file)) {
                        require($file);

                        return true;
                    }
                }
            }
        }

        return false;
    }

    $class = substr($class, $len);

    $file = \OSCOM\BASE_DIRECTORY . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    $custom = str_replace('osCommerce' . DIRECTORY_SEPARATOR . 'OM' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR, 'osCommerce' . DIRECTORY_SEPARATOR . 'OM' . DIRECTORY_SEPARATOR . 'Custom' . DIRECTORY_SEPARATOR, $file);

    if (is_file($custom)) {
        require($custom);
    } elseif (is_file($file)) {
        require($file);
    }
});

require(OSCOM::BASE_DIRECTORY . 'External/vendor/autoload.php');

OSCOM::loadConfig();
DateTime::setTimeZone();
ErrorHandler::initialize();
