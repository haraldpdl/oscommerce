<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

use osCommerce\OM\Core\{
    HTML,
    OSCOM,
    Registry
};

class ActionRecorder
{
    public static function save(array $action): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        $data = [
            'site' => $action['site'] ?? OSCOM::getSite(),
            'application' => $action['application'] ?? OSCOM::getSiteApplication(),
            'ip_address' => $action['ip_address'] ?? sprintf('%u', ip2long(OSCOM::getIPAddress()))
        ];

        if (isset($action['actions'])) {
            $actions = $action['actions'];
        } else {
            $actions = [];

            if (Registry::exists('Application')) {
                $actions = Registry::get('Application')->getRequestedActions();
            }
        }

        if (!empty($actions)) {
            $data['actions'] = implode('&', $actions);
        }

        if (isset($action['action'])) {
            $data['action'] = $action['action'];
        } else {
            trigger_error('ActionRecorder: \'action\' not defined.', E_USER_ERROR);

            $data['action'] = '';
        }

        if (isset($action['success'])) {
            $data['success'] = $action['success'];
        } else {
            trigger_error('ActionRecorder: \'success\' not defined.', E_USER_ERROR);

            $data['success'] = '';
        }

        if (isset($action['user_id'])) {
            $data['user_id'] = $action['user_id'];
        }

        if (isset($action['identifier'])) {
            $data['identifier'] = $action['identifier'];
        }

        if (isset($action['result'])) {
            $data['result'] = $action['result'];
        }

        $user_agent = $action['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '';
        $user_agent = HTML::sanitize($user_agent);

        if (mb_strlen($user_agent) > 2048) {
            $user_agent = mb_substr($user_agent, 0, 2048);
        }

        $data['user_agent'] = $user_agent;

        return ($OSCOM_PDO->save('action_recorder', $data) === 1);
    }
}
