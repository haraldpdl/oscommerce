<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

use osCommerce\OM\Core\{
    OSCOM,
    RunScriptException
};

class RunScript {
    public static $php_binary;

    public static function execute()
    {
        $site = $app = $script = null;

        if (PHP_SAPI === 'cli') {
            static::$php_binary = PHP_BINARY;

            $opts = getopt('', ['php-binary::', 'script:', 'site::', 'app::']);

            if (isset($opts['php-binary'])) {
                static::$php_binary = $opts['php-binary'];
            }

            if (isset($opts['script'])) {
                $script = $opts['script'];

                if (isset($opts['site'])) {
                    $site = $opts['site'];
                }

                if (isset($opts['app'])) {
                    $app = $opts['app'];
                }
            }
        } else {
            $runscript_key = OSCOM::getConfig('runscript_key', 'OSCOM');

            if (empty($runscript_key) || !isset($_POST['key']) || ($_POST['key'] !== $runscript_key)) {
                exit;
            }

            $script = $_GET['RunScript'];

            if (isset($_GET['site']) && !empty($_GET['site'])) {
                $site = $_GET['site'];
            }

            if (isset($_GET['app']) && !empty($_GET['app'])) {
                $app = $_GET['app'];
            }
        }

        try {
            if (!isset($script)) {
                throw new RunScriptException('Script not specified');
            }

            foreach (explode('\\', $script) as $s) {
                if (!OSCOM::isValidClassName($s)) {
                    throw new RunScriptException('Invalid script name: ' . $script);
                }
            }

            if (isset($site) && !OSCOM::siteExists($site)) {
                throw new RunScriptException('(' . $script . ') Site does not exist: ' . $site);
            }

            $script_class = 'osCommerce\\OM\\';

            if (isset($site)) {
                $script_class .= 'Core\\Site\\' . $site . '\\';
             }

             $script_class .= 'Scripts\\' . $script;

            if (!class_exists($script_class)) {
                throw new RunScriptException('Script class does not exist: ' . $script_class);
            }

            if (!is_subclass_of($script_class, 'osCommerce\\OM\\Core\\RunScriptInterface')) {
                throw new RunScriptException('Script class does not implement osCommerce\\OM\\Core\\RunScriptInterface: ' . $script_class);
            }

            OSCOM::setSite();

            call_user_func([
                $script_class,
                'execute'
            ]);
        } catch (RunScriptException | \Exception $e) {
            static::error($e->getMessage());
        }

        exit;
    }

    public static function getOpt(string $name): ?string
    {
        $result = null;

        if (PHP_SAPI === 'cli') {
            $opts = getopt('', [$name . '::']);

            if (isset($opts[$name])) {
                $result = $opts[$name];
            }
        } elseif (isset($_GET[$name])) {
            $result = $_GET[$name];
        }

        return $result;
    }

    public static function error(string $message)
    {
        if ((PHP_SAPI !== 'cli') && !headers_sent()) {
            http_response_code(400);
        }

        trigger_error('[RunScript] ' . $message);

        echo $message . ((PHP_SAPI === 'cli') ? "\n" : '<br>');
    }
}
