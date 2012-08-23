<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\HttpRequest;

  class Curl {
    public static function execute($parameters) {
      $curl = curl_init($parameters['server']['scheme'] . '://' . $parameters['server']['host'] . $parameters['server']['path'] . (isset($parameters['server']['query']) ? '?' . $parameters['server']['query'] : ''));

      $curl_options = array(CURLOPT_PORT => $parameters['server']['port'],
                            CURLOPT_HEADER => true,
                            CURLOPT_SSL_VERIFYPEER => true,
                            CURLOPT_SSL_VERIFYHOST => 2,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_FORBID_REUSE => true,
                            CURLOPT_FRESH_CONNECT => true,
                            CURLOPT_FOLLOWLOCATION => false); // does not work with open_basedir so a workaround is implemented below

      if ( !empty($parameters['header']) ) {
        $curl_options[CURLOPT_HTTPHEADER] = $parameters['header'];
      }

      if ( isset($parameters['cafile']) && file_exists($parameters['cafile']) ) {
        $curl_options[CURLOPT_CAINFO] = $parameters['cafile'];
      }

      if ( isset($parameters['certificate']) ) {
        $curl_options[CURLOPT_SSLCERT] = $parameters['certificate'];
      }

      if ( $parameters['method'] == 'post' ) {
        $curl_options[CURLOPT_POST] = true;
        $curl_options[CURLOPT_POSTFIELDS] = $parameters['parameters'];
      }

      curl_setopt_array($curl, $curl_options);
      $result = curl_exec($curl);

      if ( $result === false ) {
        trigger_error(curl_error($curl));

        curl_close($curl);

        return false;
      }

      $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      curl_close($curl);

      list($headers, $body) = explode("\r\n\r\n", $result, 2);

      if ( ($http_code == 301) || ($http_code == 302) ) {
        if ( !isset($parameters['redir_counter']) || ($parameters['redir_counter'] < 6) ) {
          if ( !isset($parameters['redir_counter']) ) {
            $parameters['redir_counter'] = 0;
          }

          $matches = array();
          preg_match('/(Location:|URI:)(.*?)\n/i', $headers, $matches);

          $redir_url = trim(array_pop($matches));

          $parameters['redir_counter']++;

          $redir_params = array('url' => $redir_url,
                                'method' => $parameters['method'],
                                'redir_counter', $parameters['redir_counter']);

          $body = \osCommerce\OM\Core\HttpRequest::getResponse($redir_params, 'Curl');
        }
      }

      return $body;
    }

    public static function canUse() {
      return function_exists('curl_init');
    }
  }
?>
