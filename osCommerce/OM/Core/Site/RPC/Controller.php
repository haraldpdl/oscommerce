<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\RPC;

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\HttpRequest;
  use osCommerce\OM\Core\OSCOM;

  class Controller implements \osCommerce\OM\Core\SiteInterface {
    const STATUS_SUCCESS = 1;
    const STATUS_REDIRECT_DETECTED = -10;
    const STATUS_NO_MODULE = -20;
    const STATUS_NO_ACCESS = -50;
    const STATUS_CLASS_NONEXISTENT = -60;
    const STATUS_NO_ACTION = -70;
    const STATUS_ACTION_NONEXISTENT = -71;

    protected static $_default_application = 'Index';

    public static function initialize() {
      header('Content-Type: application/json; charset=utf-8');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
      header('Cache-Control: no-store, no-cache, must-revalidate');
      header('Cache-Control: post-check=0, pre-check=0', false);
      header('Pragma: no-cache');

      try {
        if ( empty($_GET) ) {
          throw new \Exception(self::STATUS_NO_MODULE);
        } elseif ( count($_GET) < 3 ) {
          throw new \Exception(self::STATUS_NO_ACTION);
        }

        $site = HTML::sanitize(basename(key(array_slice($_GET, 1, 1, true))));
        $application = HTML::sanitize(basename(key(array_slice($_GET, 2, 1,  true))));

        if ( !OSCOM::siteExists($site) ) {
          throw new \Exception(self::STATUS_CLASS_NONEXISTENT);
        }

        OSCOM::setSite($site);

        if ( !OSCOM::siteApplicationExists($application) ) {
          throw new \Exception(self::STATUS_CLASS_NONEXISTENT);
        }

        OSCOM::setSiteApplication($application);

        ob_start( function($buffer) {
          foreach ( headers_list() as $h ) {
            if ( stripos($h, 'Location:') !== false ) {
              header_remove('Location');

              \osCommerce\OM\Core\HttpRequest::setResponseCode(403);

              $buffer = json_encode(array('rpcStatus' => constant('osCommerce\\OM\\Core\\Site\\RPC\\Controller::STATUS_REDIRECT_DETECTED')));

              break;
            }
          }

          return $buffer;
        });

        call_user_func(array('osCommerce\\OM\\Core\\Site\\' . $site . '\\Controller', 'initialize'));

        ob_end_flush();

        if ( !call_user_func(array('osCommerce\\OM\\Core\\Site\\' . $site . '\\Controller', 'hasAccess'), $application)) {
          throw new \Exception(self::STATUS_NO_ACCESS);
        }

        $rpc_called = false;

        $rpc = array('RPC');

        for ( $i = 3, $n = count($_GET); $i < $n; $i++ ) {
          $subrpc = HTML::sanitize(basename(key(array_slice($_GET, $i, 1, true))));

          if ( self::siteApplicationRPCExists(implode('\\', $rpc) . '\\' . $subrpc) ) {
            call_user_func(array('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\' . implode('\\', $rpc) . '\\' . $subrpc, 'execute'));

            $rpc[] = $subrpc;

            $rpc_called = true;
          } else {
            break;
          }
        }

        if ( $rpc_called === false ) {
          throw new \Exception(self::STATUS_NO_ACTION);
        }
      } catch ( \Exception $e ) {
        HttpRequest::setResponseCode(403);

        echo json_encode(array('rpcStatus' => $e->getMessage()));
      }

      exit;
    }

    public static function getDefaultApplication() {
      return self::$_default_application;
    }

    public static function hasAccess($application) {
      return true;
    }

    public static function siteApplicationRPCExists($rpc) {
      return class_exists('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\' . $rpc);
    }
  }
?>
