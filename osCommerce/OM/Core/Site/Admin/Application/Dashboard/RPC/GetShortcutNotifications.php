<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2012 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Admin\Application\Dashboard\RPC;

  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Site\Admin\Application\Dashboard\Dashboard;
  use osCommerce\OM\Core\Site\RPC\Controller as RPC;

  class GetShortcutNotifications {
    public static function execute() {
      $site = OSCOM::getSite();

      $result = array('entries' => array());

      if ( isset($_SESSION[$site]['id']) ) {
        if ( isset($_GET['reset']) && !empty($_GET['reset']) && OSCOM::siteApplicationExists($_GET['reset']) ) {
          Dashboard::updateAppDateOpened($_SESSION[$site]['id'], $_GET['reset']);
        }

        $shortcuts = array();

        foreach ( Dashboard::getShortcuts($_SESSION[$site]['id']) as $app ) {
          $shortcuts[$app['module']] = $app['last_viewed'];
        }

        foreach ( $_SESSION[$site]['access'] as $module => $data ) {
          if ( $data['shortcut'] === true ) {
            if ( method_exists('osCommerce\\OM\\Core\\Site\\Admin\\Application\\' . $data['module'] . '\\' . $data['module'], 'getShortcutNotification') || class_exists('osCommerce\\OM\\Core\\Site\\Admin\\Application\\' . $data['module'] . '\\Model\\getShortcutNotification') ) {
              $result['entries'][$data['module']] = call_user_func(array('osCommerce\\OM\\Core\\Site\\Admin\\Application\\' . $data['module'] . '\\' . $data['module'], 'getShortcutNotification'), $shortcuts[$data['module']]);
            }
          }
        }
      }

      $result['rpcStatus'] = RPC::STATUS_SUCCESS;

      echo json_encode($result);
    }
  }
?>
