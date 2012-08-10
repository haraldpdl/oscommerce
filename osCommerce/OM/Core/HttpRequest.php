<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core;

  class HttpRequest {
    protected static $_drivers = array('HttpRequest', 'Curl', 'Stream');

/**
 *
 * @param array $parameters url, header, parameters, method, certificate
 * @param string $driver
 */

    public static function getResponse($parameters, $driver = null) {
      if ( !isset($driver) ) {
        foreach ( static::$_drivers as $d ) {
          if ( call_user_func(array('osCommerce\\OM\\Core\\HttpRequest\\' . $d, 'canUse')) ) {
            $driver = $d;

            break;
          }
        }
      }

      if ( !isset($parameters['header']) || !is_array($parameters['header'])) {
        $parameters['header'] = array();
      }

      if ( !isset($parameters['parameters']) ) {
        $parameters['parameters'] = '';
      }

      if ( !isset($parameters['method']) ) {
        $parameters['method'] = 'post';
      }

      $parameters['server'] = parse_url($parameters['url']);

      if ( !isset($parameters['server']['port']) ) {
        $parameters['server']['port'] = ($parameters['server']['scheme'] == 'https') ? 443 : 80;
      }

      if ( !isset($parameters['server']['path']) ) {
        $parameters['server']['path'] = '/';
      }

      if ( isset($parameters['server']['user']) && isset($parameters['server']['pass']) ) {
        $parameters['header'][] = 'Authorization: Basic ' . base64_encode($parameters['server']['user'] . ':' . $parameters['server']['pass']);
      }

      return trim(call_user_func(array('osCommerce\\OM\\Core\\HttpRequest\\' . $driver, 'execute'), $parameters));
    }

/**
 * Set the HTTP status code
 *
 * @param int $code The HTTP status code to set
 * @return boolean
 * @since v3.0.3
 */

    public static function setResponseCode($code) {
      if ( !is_numeric($code) ) {
        trigger_error('HttpRequest::setResponseCode() - $code value is not numeric.', E_USER_ERROR);

        return false;
      }

      if ( headers_sent() ) {
        trigger_error('HttpRequest::setResponseCode() - headers already sent, cannot set response code.', E_USER_ERROR);

        return false;
      }

      if ( function_exists('http_response_code') ) { /* HPDL PHP 5.4.0 */
        http_response_code($code);

        return true;
      } else {
        $codes = array('100' => 'Continue',
                       '101' => 'Switching Protocols',
                       '200' => 'OK',
                       '201' => 'Created',
                       '202' => 'Accepted',
                       '203' => 'Non-Authoritative Information',
                       '204' => 'No Content',
                       '205' => 'Reset Content',
                       '206' => 'Partial Content',
                       '300' => 'Multiple Choices',
                       '301' => 'Moved Permanently',
                       '302' => 'Moved Temporarily',
                       '303' => 'See Other',
                       '304' => 'Not Modified',
                       '305' => 'Use Proxy',
                       '400' => 'Bad Request',
                       '401' => 'Unauthorized',
                       '402' => 'Payment Required',
                       '403' => 'Forbidden',
                       '404' => 'Not Found',
                       '405' => 'Method Not Allowed',
                       '406' => 'Not Acceptable',
                       '407' => 'Proxy Authentication Required',
                       '408' => 'Request Time-out',
                       '409' => 'Conflict',
                       '410' => 'Gone',
                       '411' => 'Length Required',
                       '412' => 'Precondition Failed',
                       '413' => 'Request Entity Too Large',
                       '414' => 'Request-URI Too Large',
                       '415' => 'Unsupported Media Type',
                       '500' => 'Internal Server Error',
                       '501' => 'Not Implemented',
                       '502' => 'Bad Gateway',
                       '503' => 'Service Unavailable',
                       '504' => 'Gateway Time-out',
                       '505' => 'HTTP Version not supported');

        if ( isset($codes[$code]) ) {
          $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

          header($protocol . ' ' . $code . ' ' . $codes[$code]);

          return true;
        } else {
          trigger_error('HttpRequest::setResponseCode() - Unknown status code \'' . $code . '\'.', E_USER_ERROR);
        }
      }

      return false;
    }
  }
?>
