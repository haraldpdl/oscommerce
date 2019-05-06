<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

class OSCOM
{
    const BASE_DIRECTORY = \OSCOM\BASE_DIRECTORY;
    const PUBLIC_DIRECTORY = \OSCOM\PUBLIC_BASE_DIRECTORY;

    const VALID_CLASS_NAME_REGEXP = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/'; // https://php.net/manual/en/language.oop5.basic.php

    protected static $version;
    protected static $request_type;
    protected static $site;
    protected static $application;
    protected static $is_rpc = false;
    protected static $config;

    public static function initialize(string $site = null)
    {
        static::setSite($site);

        if (!static::siteExists(static::getSite())) {
            trigger_error('Site \'' . static::getSite() . '\' does not exist', E_USER_ERROR);
            exit;
        }

        static::checkOffline();

        static::setSiteApplication();

        call_user_func(['osCommerce\\OM\\Core\\Site\\' . static::getSite() . '\\Controller', 'initialize']);
    }

    public static function siteExists(string $site): bool
    {
        return static::isValidClassName($site) && class_exists('osCommerce\\OM\\Core\\Site\\' . $site . '\\Controller');
    }

    public static function setSite(string $site = null)
    {
        if (isset($site)) {
            if (!static::siteExists($site)) {
                trigger_error('Site \'' . $site . '\' does not exist, using \'' . (static::$site ?? static::getDefaultSite()) . '\'', E_USER_ERROR);

                unset($site);
            }
        } else {
            if (!empty($_GET)) {
                $requested_site = HTML::sanitize(basename(key(array_slice($_GET, 0, 1, true))));

                if (preg_match('/^[A-Z][A-Za-z0-9-_]*$/', $requested_site) && static::siteExists($requested_site)) {
                    $site = $requested_site;
                }
            }
        }

        static::$site = $site ?? static::$site ?? static::getDefaultSite();
    }

    public static function getSite(): ?string
    {
        return static::$site;
    }

    public static function getSites(): array
    {
        $sites = [];

        $OSCOM_DL = new DirectoryListing(static::BASE_DIRECTORY . 'Core/Site/');
        $OSCOM_DL->setIncludeFiles(false);

        foreach ($OSCOM_DL->getFiles() as $f) {
            $sites[] = $f['name'];
        }

        $OSCOM_DL = new DirectoryListing(static::BASE_DIRECTORY . 'Custom/Site/');
        $OSCOM_DL->setIncludeFiles(false);

        foreach ($OSCOM_DL->getFiles() as $f) {
            if (!in_array($f['name'], $sites)) {
                $sites[] = $f['name'];
            }
        }

        return $sites;
    }

    public static function getDefaultSite(): string
    {
        static $site;

        if (!isset($site)) {
            $site = static::getConfig('default_site', 'OSCOM');

            foreach (static::getSites() as $s) {
                if (!isset(static::$config[$s])) {
                    static::loadConfig($s);
                }
            }

            if (isset($_SERVER['SERVER_NAME'])) {
                $server = HTML::sanitize($_SERVER['SERVER_NAME']);

                $sites = [];

                foreach (static::$config as $group => $key) {
                    if (isset($key['http_server']) || isset($key['https_server'])) {
                        if ((isset($key['http_server']) && ('http://' . $server == $key['http_server'])) || (isset($key['https_server']) && ('https://' . $server == $key['https_server']))) {
                            $sites[] = $group;
                        }
                    }
                }

                if (count($sites) > 0) {
                    if (!in_array($site, $sites)) {
                        $site = $sites[0];
                    }
                }
            }
        }

        return $site;
    }

    public static function siteApplicationExists(string $application): bool
    {
        return static::isValidClassName($application) && class_exists('osCommerce\\OM\\Core\\Site\\' . static::getSite() . '\\Application\\' . $application . '\\Controller');
    }

    public static function setSiteApplication(string $application = null)
    {
        if (isset($application)) {
            if (!static::siteApplicationExists($application)) {
                trigger_error('Application \'' . $application . '\' does not exist for Site \'' . static::getSite() . '\', using default \'' . static::getDefaultSiteApplication() . '\'', E_USER_ERROR);
                $application = null;
            }
        } else {
            if (!empty($_GET)) {
                $requested_application = HTML::sanitize(basename(key(array_slice($_GET, 0, 1, true))));

                if ($requested_application == static::getSite()) {
                    $requested_application = HTML::sanitize(basename(key(array_slice($_GET, 1, 1, true))));
                }

                if (preg_match('/^[A-Za-z0-9-_]+$/', $requested_application) && static::siteApplicationExists($requested_application)) {
                    $application = $requested_application;
                }
            }
        }

        if (empty($application)) {
            $application = static::getDefaultSiteApplication();
        }

        static::$application = $application;
    }

    public static function getSiteApplication(): ?string
    {
        return static::$application;
    }

    public static function getDefaultSiteApplication(): string
    {
        return call_user_func(['osCommerce\\OM\\Core\\Site\\' . static::getSite() . '\\Controller', 'getDefaultApplication']);
    }

    public static function setIsRPC()
    {
        static::$is_rpc = true;
    }

    public static function isRPC(): bool
    {
        return static::$is_rpc;
    }

    public static function loadConfig(string $site = null)
    {
        if (!isset($site)) {
            $site = 'OSCOM';
        }

        $file = static::BASE_DIRECTORY;

        if ($site !== 'OSCOM') {
            $file .= 'Core/Site/' . basename($site) . '/';
        }

        $file .= 'Config/settings.ini';

        while (true) {
            if (is_file($file)) {
                $ini = parse_ini_file($file);

                static::$config[$site] = array_merge(static::$config[$site] ?? [], $ini);
            }

            $local_file = dirname($file) . '/local.ini';

            if (is_file($local_file)) {
                $local = parse_ini_file($local_file);

                static::$config[$site] = array_merge(static::$config[$site] ?? [], $local);
            }

            if (mb_strpos($file, '/Core/Site/') !== false) {
                $file = str_replace('/Core/Site/', '/Custom/Site/', $file);

                continue;
            }

            break;
        }

        if (!isset(static::$config[$site]['http_server']) && isset(static::$config[$site]['urls'])) {
            $server = isset($_SERVER['SERVER_NAME']) ? HTML::sanitize($_SERVER['SERVER_NAME']) : null;

            $urls = [];

            foreach (static::$config[$site]['urls'] as $k => $v) {
                [$alias, $param] = explode('.', $k, 2);

                $urls[$alias][$param] = $v;
            }

            if (isset($urls['default'])) {
                $url = $urls['default'];

                foreach ($urls as $k => $v) {
                    if (((static::getRequestType() === 'NONSSL') && ('http://' . $server == $v['http_server'])) || (isset($v['enable_ssl']) && ($v['enable_ssl'] === 'true') && isset($v['https_server']) && ('https://' . $server == $v['https_server']))) {
                        $url = $urls[$k];
                        $url['urls_key'] = $k;
                        break;
                    }
                }

                static::$config[$site] = array_merge(static::$config[$site], $url);
            }
        }
    }

    public static function getConfig(string $key, string $group = null)
    {
        if (!isset($group)) {
            $group = static::getSite();
        }

        if (!isset(static::$config[$group])) {
            static::loadConfig($group);
        }

        return static::$config[$group][$key];
    }

    public static function configExists(string $key, string $group = null): bool
    {
        if (!isset($group)) {
            $group = static::getSite();
        }

        if (!isset(static::$config[$group])) {
            static::loadConfig($group);
        }

        return isset(static::$config[$group][$key]);
    }

    public static function setConfig(string $key, $value, string $group = null)
    {
        if (!isset($group)) {
            $group = static::getSite();
        }

        static::$config[$group][$key] = $value;
    }

    public static function getVersion(string $site = 'OSCOM'): ?string
    {
        if (!isset(static::$version[$site])) {
            if ($site == 'OSCOM') {
                $file = static::BASE_DIRECTORY . 'version.txt';
            } else {
                if (!static::siteExists($site)) {
                    trigger_error('OSCOM::getVersion(): Site "' . $site . '" does not exist.');

                    return null;
                }

                $file = static::BASE_DIRECTORY . 'Custom/Site/' . $site . '/version.txt';

                if (!file_exists($file)) {
                    $file = static::BASE_DIRECTORY . 'Core/Site/' . $site . '/version.txt';
                }
            }

            if (!file_exists($file)) {
                trigger_error('OSCOM::getVersion(): Version file does not exist: ' . $file);

                return null;
            }

            $v = trim(file_get_contents($file));

            if (preg_match('/^(\d+\.)?(\d+\.)?(\d+)$/', $v)) {
                static::$version[$site] = $v;
            } else {
                trigger_error('Version number is not numeric. Please verify: ' . $file);

                return null;
            }
        }

        return static::$version[$site];
    }

    protected static function setRequestType()
    {
        static::$request_type = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on') ? 'SSL' : 'NONSSL');
    }

    public static function getRequestType(): string
    {
        if (!isset(static::$request_type)) {
            static::setRequestType();
        }

        return static::$request_type;
    }

    public static function getBaseUrl(string $site = null, string $connection = 'SSL', bool $with_index_file = true): string
    {
        if (empty($site)) {
            $site = static::getSite();
        }

        if (!in_array($connection, ['NONSSL', 'SSL', 'AUTO'])) {
            $connection = 'SSL';
        }

        $link = '';

        if ($connection == 'AUTO') {
            if ((static::getRequestType() == 'SSL') && (static::getConfig('enable_ssl', $site) == 'true')) {
                $link = static::getConfig('https_server', $site) . static::getConfig('dir_ws_https_server', $site);
            } else {
                $link = static::getConfig('http_server', $site) . static::getConfig('dir_ws_http_server', $site);
            }
        } elseif (($connection == 'SSL') && (static::getConfig('enable_ssl', $site) == 'true')) {
            $link = static::getConfig('https_server', $site) . static::getConfig('dir_ws_https_server', $site);
        } else {
            $link = static::getConfig('http_server', $site) . static::getConfig('dir_ws_http_server', $site);
        }

        if ($with_index_file === true) {
            $index_file = static::getConfig('index_file', 'OSCOM');

            if (!empty($index_file)) {
                $link .= $index_file;
            }
        }

        return $link;
    }

/**
 * Return an internal URL address.
 *
 * @param string $site The Site to link to. Default: The currently used Site.
 * @param string $application The Site Application to link to. Default: The currently used Site Application.
 * @param string $parameters Parameters to add to the link. Example: key1=value1&key2=value2
 * @param string $connection The type of connection to use for the link. Values: NONSSL, SSL, AUTO. Default: SSL.
 * @param bool $add_session_id Add the session ID to the link. Default: True.
 * @param bool $search_engine_safe Use search engine safe URLs. Default: True.
 * @return string The URL address.
 */

    public static function getLink(string $site = null, string $application = null, string $parameters = null, string $connection = 'SSL', bool $add_session_id = true, bool $search_engine_safe = true): string
    {
        if (empty($site)) {
            $site = static::getSite();
        }

        if (empty($application) && ($site == static::getSite())) {
            $application = static::getSiteApplication();
        }

        if (!in_array($connection, ['NONSSL', 'SSL', 'AUTO'])) {
            $connection = 'SSL';
        }

// Wrapper for RPC links; RPC cannot perform cross domain requests
        $real_site = ($site == 'RPC') ? $application : $site;

        if ($connection == 'AUTO') {
            if ((static::getRequestType() == 'SSL') && (static::getConfig('enable_ssl', $real_site) == 'true')) {
                $link = static::getConfig('https_server', $real_site) . static::getConfig('dir_ws_https_server', $real_site);
            } else {
                $link = static::getConfig('http_server', $real_site) . static::getConfig('dir_ws_http_server', $real_site);
            }
        } elseif (($connection == 'SSL') && (static::getConfig('enable_ssl', $real_site) == 'true')) {
            $link = static::getConfig('https_server', $real_site) . static::getConfig('dir_ws_https_server', $real_site);
        } else {
            $link = static::getConfig('http_server', $real_site) . static::getConfig('dir_ws_http_server', $real_site);
        }

        $link .= static::getConfig('index_file', 'OSCOM') . static::getConfig('query_string_initiator', 'OSCOM');

        if ($site != static::getDefaultSite()) {
            $link .= $site . '&';
        }

        if (!empty($application) && ($application != static::getDefaultSiteApplication())) {
            $link .= $application . '&';
        }

        if (!empty($parameters)) {
            $link .= HTML::output($parameters) . '&';
        }

        if (($add_session_id === true) && Registry::exists('Session') && Registry::get('Session')->hasStarted() && ((bool)ini_get('session.use_only_cookies') === false)) {
            if (strlen(SID) > 0) {
                $_sid = SID;
            } elseif (((static::getRequestType() == 'NONSSL') && ($connection == 'SSL') && (static::getConfig('enable_ssl', $site) == 'true')) || ((static::getRequestType() == 'SSL') && ($connection != 'SSL'))) {
                if (static::getConfig('http_cookie_domain', $site) != static::getConfig('https_cookie_domain', $site)) {
                    $_sid = Registry::get('Session')->getName() . '=' . Registry::get('Session')->getID();
                }
            }
        }

        if (isset($_sid)) {
            $link .= HTML::output($_sid);
        }

        while ((substr($link, -1) == '&') || (substr($link, -1) == static::getConfig('query_string_initiator', 'OSCOM'))) {
            $link = substr($link, 0, -1);
        }

        if (($search_engine_safe === true) && Registry::exists('osC_Services') && Registry::get('osC_Services')->isStarted('sefu')) {
            $link = str_replace([static::getConfig('query_string_initiator', 'OSCOM'), '&', '='], ['/', '/', ','], $link);
        }

        return $link;
    }

/**
 * Return an internal URL address for public objects.
 *
 * @param string $url The object location from the public/sites/SITE/ directory.
 * @param string $parameters Parameters to add to the link. Example: key1=value1&key2=value2
 * @param string $site Get a public link from a specific Site
 * @return string The URL address.
 */

    public static function getPublicSiteLink(string $url, string $parameters = null, string $site = null): string
    {
        if (!isset($site)) {
            $site = static::getSite();
        }

        $link = 'public/sites/' . $site . '/' . $url;

        if (!empty($parameters)) {
            $link .= '?' . HTML::output($parameters);
        }

        while ((substr($link, -1) == '&') || (substr($link, -1) == '?')) {
            $link = substr($link, 0, -1);
        }

        return $link;
    }

/**
 * Return an internal URL address for an RPC call.
 *
 * @param string $site The Site to link to. Default: The currently used Site.
 * @param string $application The Site Application to link to. Default: The currently used Site Application.
 * @param string $parameters Parameters to add to the link. Example: key1=value1&key2=value2
 * @param string $connection The type of connection to use for the link. Values: NONSSL, SSL, AUTO. Default: AUTO.
 * @param bool $add_session_id Add the session ID to the link. Default: True.
 * @param bool $search_engine_safe Use search engine safe URLs. Default: True.
 * @return string The URL address.
 */

    public static function getRPCLink(string $site = null, string $application = null, string $parameters = null, string $connection = 'SSL', bool $add_session_id = true, bool $search_engine_safe = true): string
    {
        if (empty($site)) {
            $site = static::getSite();
        }

        if (empty($application)) {
            $application = static::getSiteApplication();
        }

        return static::getLink('RPC', $site, $application . '&' . $parameters, $connection, $add_session_id, $search_engine_safe);
    }

    public static function redirect(string $url, int $http_response_code = null)
    {
        if ((strpos($url, "\n") !== false) || (strpos($url, "\r") !== false)) {
            $url = static::getLink(OSCOM::getDefaultSite());
        }

        if (strpos($url, '&amp;') !== false) {
            $url = str_replace('&amp;', '&', $url);
        }

        header('Location: ' . $url, true, $http_response_code);

        exit;
    }

/**
 * Return a language definition
 *
 * @param string $key The language definition to return
 * @param array $values Replace keywords with values (@since v3.0.3)
 * @return string The language definition
 */

    public static function getDef(string $key, array $values = null): string
    {
        $def = Registry::get('Language')->get($key);

        if (!empty($values)) {
            $def = str_replace(array_keys($values), array_values($values), $def);
        }

        return $def;
    }

/**
 * Execute database queries
 *
 * @param string $procedure The name of the database query to execute
 * @param array $data Parameters passed to the database query
 * @param string $type The namespace type the database query is stored in [ Core, Site, CoreUpdate (@since v3.0.2), Application (default) ]
 * @return mixed The result of the database query
 */
    public static function callDB(string $procedure, array $data = null, string $type = 'Application')
    {
        $OSCOM_PDO = Registry::get('PDO');

        $call = explode('\\', $procedure);

        switch ($type) {
            case 'Core':
                $procedure = array_pop($call);
                $ns = 'osCommerce\\OM\\Core';

                if (!empty($call)) {
                    $ns .= '\\' . implode('\\', $call);
                }

                break;

            case 'Site':
                $ns = 'osCommerce\\OM\\Core\\Site\\' . $call[0];
                $procedure = $call[1];

                break;

            case 'CoreUpdate':
                $ns = 'osCommerce\\OM\\Work\\CoreUpdate\\' . $call[0];
                $procedure = $call[1];

                break;

            case 'Application':
            default:
                $ns = 'osCommerce\\OM\\Core\\Site\\' . $call[0] . '\\Application\\' . $call[1];
                $procedure = $call[2];
        }

        $db_driver = $OSCOM_PDO->getDriver();

        if (!class_exists($ns . '\\SQL\\' . $db_driver . '\\' . $procedure)) {
            if ($OSCOM_PDO->hasDriverParent() && class_exists($ns . '\\SQL\\' . $OSCOM_PDO->getDriverParent() . '\\' . $procedure)) {
                $db_driver = $OSCOM_PDO->getDriverParent();
            } else {
                $db_driver = 'ANSI';
            }
        }

        return call_user_func([$ns . '\\SQL\\' . $db_driver . '\\' . $procedure, 'execute'], $data);
    }

/**
 * Set a cookie
 *
 * @param string $name The name of the cookie
 * @param string $value The value of the cookie
 * @param int $expires Unix timestamp of when the cookie should expire
 * @param string $path The path on the server for which the cookie will be available on
 * @param string $domain The The domain that the cookie is available on
 * @param boolean $secure Indicates whether the cookie should only be sent over a secure HTTPS connection
 * @param boolean $httpOnly Indicates whether the cookie should only accessible over the HTTP protocol
 * @return boolean
 * @since v3.0.0
 */

    public static function setCookie(string $name, string $value = null, int $expires = 0, string $path = null, string $domain = null, bool $secure = false, bool $httpOnly = false): bool
    {
        if (!isset($path)) {
            $path = (static::getRequestType() == 'NONSSL') ? static::getConfig('http_cookie_path') : static::getConfig('https_cookie_path');
        }

        if (!isset($domain)) {
            $domain = (static::getRequestType() == 'NONSSL') ? static::getConfig('http_cookie_domain') : static::getConfig('https_cookie_domain');
        }

        return setcookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
    }

/**
 * Get the IP address of the client
 *
 * @since v3.0.0
 */

    public static function getIPAddress(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    }

/**
 * Get all parameters in the GET scope
 *
 * @param array $exclude A list of parameters to exclude
 * @return string
 * @since v3.0.0
 */

    public static function getAllGET($exclude = null): string
    {
        if (!is_array($exclude)) {
            if (!empty($exclude)) {
                $exclude = [$exclude];
            } else {
                $exclude = [];
            }
        }

        $array = [
            static::getSite(),
            static::getSiteApplication(),
            'error',
            'x',
            'y'
        ];

        if (Registry::exists('Session')) {
            $array[] = Registry::get('Session')->getName();
        }

        $exclude = array_merge($exclude, $array);

        $params = '';

        foreach ($_GET as $key => $value) {
            if (!in_array($key, $exclude)) {
                $params .= $key . (!empty($value) ? '=' . $value : '') . '&';
            }
        }

        if (!empty($params)) {
            $params = substr($params, 0, -1);
        }

        return $params;
    }

    public static function isValidClassName(string $classname): bool
    {
        return preg_match(static::VALID_CLASS_NAME_REGEXP, $classname) === 1;
    }

    public static function checkOffline()
    {
        if ((static::configExists('offline', 'OSCOM') && (static::getConfig('offline', 'OSCOM') === 'true')) || (static::configExists('offline') && (static::getConfig('offline') === 'true'))) {
            if (RunScript::$is_running === true) {
                if (RunScript::$override_offline === true) {
                    return true;
                }

                RunScript::error('Currently in maintenance mode. Use --override-offline to continue executing the script.');
            } else {
                HttpRequest::setResponseCode(503);

                $content = ErrorHandler::getErrorPageContents('maintenance');

                if (!is_null($content)) {
                    echo $content;
                }
            }

            exit;
        }
    }
}
