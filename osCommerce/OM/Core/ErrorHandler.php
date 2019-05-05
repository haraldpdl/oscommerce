<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

use osCommerce\OM\Core\{
    DateTime,
    HttpRequest,
    Language,
    OSCOM,
    PDO,
    Registry
};

class ErrorHandler
{
    static protected $dbh;

    public static function initialize()
    {
        ini_set('display_errors', false);
        ini_set('html_errors', false);
        ini_set('ignore_repeated_errors', true);

        if (is_writable(OSCOM::BASE_DIRECTORY . 'Work/Logs')) {
            ini_set('log_errors', true);
            ini_set('error_log', OSCOM::BASE_DIRECTORY . 'Work/Logs/errors.txt');
        }

        if (in_array('sqlite', \PDO::getAvailableDrivers()) && is_writable(OSCOM::BASE_DIRECTORY . 'Work/Database/')) {
            set_error_handler('osCommerce\\OM\\Core\\ErrorHandler::execute');

            if (file_exists(OSCOM::BASE_DIRECTORY . 'Work/Logs/errors.txt')) {
                static::import(OSCOM::BASE_DIRECTORY . 'Work/Logs/errors.txt');
            }
        }

        register_shutdown_function('osCommerce\\OM\\Core\\ErrorHandler::onShutdown');
    }

    public static function execute(int $errno, string $errstr, string $errfile, int $errline)
    {
        if (!is_resource(static::$dbh) && !static::connect()) {
            return false;
        }

        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $errors = 'Notice';
                break;

            case E_WARNING:
            case E_USER_WARNING:
                $errors = 'Warning';
                break;

            case E_ERROR:
            case E_USER_ERROR:
                $errors = 'Fatal Error';
                break;

            default:
                $errors = 'Unknown';
        }

        $errstr = Language::toUTF8($errstr);

        $error_msg = sprintf('PHP %s: %s in %s on line %d', $errors, $errstr, $errfile, $errline);

        try {
            static::$dbh->beginTransaction();

            static::$dbh->save('error_log', [
                'timestamp' => time(),
                'message' => $error_msg
            ]);

            static::$dbh->commit();
        } catch (\Exception $e) {
            static::$dbh->rollBack();
        }

// return true to stop further processing of internal php error handler
        return true;
    }

    public static function connect()
    {
        $result = false;

        try {
            static::$dbh = PDO::initialize(OSCOM::BASE_DIRECTORY . 'Work/Database/errors.sqlite3', null, null, null, null, 'SQLite3', [], ['prefix_tables' => false]);
            static::$dbh->exec('create table if not exists error_log ( timestamp int, message text );');

            $result = true;
        } catch (\Exception $e) {
            trigger_error($e->getMessage());
        }

        return $result;
    }

    public static function getAll(int $limit = null, int $pageset = null)
    {
        if (!is_resource(static::$dbh) && !static::connect()) {
            return [];
        }

        $limit_q = null;

        if (isset($limit)) {
            $limit_q = $limit;

            if (isset($pageset)) {
                $offset = max(($pageset * $limit) - $limit, 0);

                $limit_q = [$offset, $limit];
            }
        }

        return static::$dbh->get('error_log', [
            'timestamp',
            'message'
        ], null, 'rowid desc', $limit_q)->fetchAll();
    }

    public static function getTotalEntries(): int
    {
        if (!is_resource(static::$dbh) && !static::connect()) {
            return 0;
        }

        $result = self::$dbh->get('error_log', 'count(*) as total')->fetch();

        return $result['total'] ?? 0;
    }

    public static function find(string $search, int $limit = null, int $pageset = null)
    {
        if (!is_resource(static::$dbh) && !static::connect()) {
            return [];
        }

        $limit_q = null;

        if (isset($limit)) {
            $limit_q = $limit;

            if (isset($pageset)) {
                $offset = max(($pageset * $limit) - $limit, 0);

                $limit_q = [$offset, $limit];
            }
        }

        return static::$dbh->get('error_log', [
            'timestamp',
            'message'
        ], [
            'message' => [
                'op' => 'like',
                'val' => '%' . $search . '%'
            ]
        ], 'rowid desc', $limit_q)->fetchAll();
    }

    public static function getTotalFindEntries(string $search): int
    {
        if (!is_resource(static::$dbh) && !static::connect()) {
            return 0;
        }

        $result = self::$dbh->get('error_log', 'count(*) as total', [
            'message' => [
                'op' => 'like',
                'val' => '%' . $search . '%'
            ]
        ])->fetch();

        return $result['total'] ?? 0;
    }

    public static function import(string $filename)
    {
        if (!is_resource(static::$dbh) && !static::connect()) {
            return false;
        }

        $error_log = file($filename);
        unlink($filename);

        $messages = [];

        foreach ($error_log as $error) {
            $error = Language::toUTF8(trim($error));

            if (preg_match('/^\[([0-9]{2}-[A-Za-z]{3}-[0-9]{4} [0-9]{2}:[0-5][0-9]:[0-5][0-9].*?)\] (.*)$/', $error, $matches)) {
                if (mb_strlen($matches[1]) == 20) {
                    $timestamp = DateTime::getTimestamp($matches[1], 'd-M-Y H:i:s');
                } else { // with timezone
                    $timestamp = DateTime::getTimestamp($matches[1], 'd-M-Y H:i:s e');
                }

                $message = $matches[2];

                $messages[] = [
                    'timestamp' => $timestamp,
                    'message' => $message
                ];
            } elseif (!empty($messages)) {
                $messages[(count($messages)-1)]['message'] .= "\n" . $error;
            } else {
                $messages[] = [
                    'timestamp' => time(),
                    'message' => $error
                ];
            }
        }

        foreach ($messages as $error) {
            static::$dbh->save('error_log', [
                'timestamp' => $error['timestamp'],
                'message' => $error['message']
            ]);
        }
    }

    public static function clear()
    {
        if (!is_resource(static::$dbh) && !static::connect()) {
            return false;
        }

        return static::$dbh->exec('drop table if exists error_log');
    }

    public static function onShutdown()
    {
        if (error_get_last() !== null) {
            if (php_sapi_name() === 'cli') {
                return true;
            }

            if (headers_sent()) {
                return true;
            }

            try {
                if (!OSCOM::isRPC()) {
                    trigger_error('$_GET = ' . json_encode($_GET));

                    HttpRequest::setResponseCode(503);

                    $page = static::getErrorPageContents();

                    if (!is_null($page)) {
                        echo $page;
                    }

                    exit;
                }
            } catch (\Exception $e) {
            }
        }
    }

    public static function getErrorPageContents(string $page = 'general'): ?string
    {
        if (isset($page)) {
            $page = basename($page);
        }

        $site = OSCOM::getSite();
        $content = null;

        if (!is_null($site)) {
            if (Registry::exists('Template') && Registry::exists('Language')) {
                $OSCOM_Template = Registry::get('Template');

                $file = OSCOM::BASE_DIRECTORY . 'Custom/Site/' . OSCOM::getSite() . '/Template/' . $OSCOM_Template->getCode() . '/ErrorHandler/pages/' . $page . '.html';

                if (!is_file($file)) {
                    $file = OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSite() . '/Template/' . $OSCOM_Template->getCode() . '/ErrorHandler/pages/' . $page . '.html';
                }

                if (is_file($file)) {
                    if (!$OSCOM_Template->valueExists('html_base_href')) {
                        $OSCOM_Template->setValue('html_base_href', $OSCOM_Template->getBaseUrl());
                    }

                    $content = $OSCOM_Template->getContent($file);
                }
            } else {
                $file = OSCOM::BASE_DIRECTORY . 'Custom/Site/' . OSCOM::getSite() . '/ErrorHandler/pages/' . $page . '.html';

                if (!is_file($file)) {
                    $file = OSCOM::BASE_DIRECTORY . 'Core/Site/' . OSCOM::getSite() . '/ErrorHandler/pages/' . $page . '.html';
                }

                if (is_file($file)) {
                    $content = file_get_contents($file);
                }
            }
        }

        if (is_null($content)) {
            $file = OSCOM::BASE_DIRECTORY . 'Custom/ErrorHandler/pages/' . $page . '.html';

            if (!is_file($file)) {
                $file = OSCOM::BASE_DIRECTORY . 'Core/ErrorHandler/pages/' . $page . '.html';
            }

            if (is_file($file)) {
                $content = file_get_contents($file);
            } else {
                trigger_error('ErrorHandler::getErrorPageContents(): Page "' . $page . '" not found.');
            }
        }

        return $content;
    }
}
