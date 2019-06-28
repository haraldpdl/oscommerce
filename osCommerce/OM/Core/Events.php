<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

use osCommerce\OM\Core\{
    DirectoryListing,
    OSCOM
};

class Events
{
    protected static $data = [];

    public static function getWatches(string $event = null): array
    {
        if (isset($event)) {
            if (isset(static::$data[$event])) {
                return static::$data[$event];
            }

            return [];
        }

        return static::$data;
    }

    public static function watch(string $event, $function)
    {
        static::$data[$event][] = $function;
    }

    public static function fire(string $event, ...$params)
    {
        if (isset(static::$data[$event])) {
            foreach (static::$data[$event] as $f) {
                call_user_func($f, ...$params);
            }
        }
    }

    public static function scan()
    {
        $paths = [
            'Core',
            'Custom'
        ];

        $site = OSCOM::getSite();

        foreach ($paths as $path) {
            if (file_exists(OSCOM::BASE_DIRECTORY . $path . '/Site/' . $site . '/Module/Event')) {
                $modules = new DirectoryListing(OSCOM::BASE_DIRECTORY . $path . '/Site/' . $site . '/Module/Event');
                $modules->setIncludeFiles(false);

                foreach ($modules->getFiles() as $module) {
                    $files = new DirectoryListing($modules->getDirectory() . '/' . $module['name']);
                    $files->setIncludeDirectories(false);
                    $files->setCheckExtension('php');

                    foreach ($files->getFiles() as $file) {
                        if (($path == 'Custom') && file_exists(OSCOM::BASE_DIRECTORY . 'Core/Site/' . $site . '/Module/Event/' . $module['name'] . '/' . $file['name'])) {
                            // custom module already loaded through autoloader
                            continue;
                        }

                        $class = 'osCommerce\\OM\\Core\\Site\\' . $site . '\\Module\\Event\\' . $module['name'] . '\\' . basename($file['name'], '.php');

                        if (is_subclass_of($class, 'osCommerce\\OM\\Core\\Module\\EventAbstract')) {
                            $e = new $class();

                            foreach ($e->getWatches() as $event => $fire) {
                                foreach ($fire as $f) {
                                    static::watch($event, $f);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
