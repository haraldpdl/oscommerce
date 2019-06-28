<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

class Access
{
    public static function getUserLevels(int $id, string $site = null): array
    {
        if (!isset($site)) {
            $site = OSCOM::getSite();
        }

        $data = [
            'id' => $id
        ];

        $applications = [];

        foreach (OSCOM::callDB('GetAccessUserLevels', $data, 'Core') as $am) {
            $applications[] = $am['module'];
        }

        if (in_array('*', $applications)) {
            $applications = [];
            $guest_applications = [];

            $callable = [
                'osCommerce\\OM\\Core\\Site\\' . $site . '\\Controller',
                'getGuestApplications'
            ];

            if (is_callable($callable)) {
                $guest_applications = call_user_func($callable);
            }

            $DLapps = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Core/Site/' . $site . '/Application');
            $DLapps->setIncludeFiles(false);

            foreach ($DLapps->getFiles() as $file) {
                if (!in_array($file['name'], $guest_applications) && is_file($DLapps->getDirectory() . '/' . $file['name'] . '/Controller.php')) {
                    $applications[] = $file['name'];
                }
            }

            $DLcapps = new DirectoryListing(OSCOM::BASE_DIRECTORY . 'Custom/Site/' . $site . '/Application');
            $DLcapps->setIncludeFiles(false);

            foreach ($DLcapps->getFiles() as $file) {
                if (!in_array($file['name'], $applications) && !in_array($file['name'], $guest_applications) && is_file($DLcapps->getDirectory() . '/' . $file['name'] . '/Controller.php')) {
                    $applications[] = $file['name'];
                }
            }
        }

        $shortcuts = [];

        foreach (OSCOM::callDB('GetAccessUserShortcuts', $data, 'Core') as $as) {
            $shortcuts[] = $as['module'];
        }

        $levels = [];

        foreach ($applications as $app) {
            $application_class = 'osCommerce\\OM\\Core\\Site\\' . $site . '\\Application\\' . $app . '\\Controller';

            if (class_exists($application_class)) {
                if (Registry::exists('Application') && ($app == OSCOM::getSiteApplication())) {
                    $OSCOM_Application = Registry::get('Application');
                } else {
                    Registry::get('Language')->loadIniFile($app . '.php');
                    $OSCOM_Application = new $application_class(false);
                }

                $levels[$app] = [
                    'module' => $app,
                    'icon' => $OSCOM_Application->getIcon(),
                    'title' => $OSCOM_Application->getTitle(),
                    'group' => $OSCOM_Application->getGroup(),
                    'linkable' => $OSCOM_Application->canLinkTo(),
                    'shortcut' => in_array($app, $shortcuts),
                    'sort_order' => $OSCOM_Application->getSortOrder()
                ];
            }
        }

        return $levels;
    }

    public static function getShortcuts(string $site = null): array
    {
        if (!isset($site)) {
            $site = OSCOM::getSite();
        }

        $shortcuts = [];

        if (isset($_SESSION[$site]['id'])) {
            foreach ($_SESSION[$site]['access'] as $module => $data) {
                if ($data['shortcut'] === true) {
                    $shortcuts[$module] = $data;
                }
            }

            ksort($shortcuts);
        }

        return $shortcuts;
    }

    public static function hasShortcut(string $site = null): bool
    {
        if (!isset($site)) {
            $site = OSCOM::getSite();
        }

        if (isset($_SESSION[$site]['id'])) {
            foreach ($_SESSION[$site]['access'] as $module => $data) {
                if ($data['shortcut'] === true) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function isShortcut(string $application, string $site = null): bool
    {
        if (!isset($site)) {
            $site = OSCOM::getSite();
        }

        if (isset($_SESSION[$site]['id'])) {
            return $_SESSION[$site]['access'][$application]['shortcut'];
        }

        return false;
    }

    public static function getLevels(string $group = null, string $site = null): array
    {
        if (!isset($site)) {
            $site = OSCOM::getSite();
        }

        $access = [];

        if (isset($_SESSION[$site]['id']) && isset($_SESSION[$site]['access'])) {
            foreach ($_SESSION[$site]['access'] as $module => $data) {
                if (($data['linkable'] === true) && (!isset($group) || ($group == $data['group']))) {
                    if (!isset($access[$data['group']][$data['sort_order']])) {
                        $access[$data['group']][$data['sort_order']] = $data;
                    } else {
                        $access[$data['group']][] = $data;
                    }
                }
            }

            ksort($access);

            foreach ($access as $group => $modules) {
                ksort($access[$group]);
            }
        }

        return $access;
    }

    public static function getGroupTitle(string $group): string
    {
        $OSCOM_Language = Registry::get('Language');

        if (!$OSCOM_Language->isDefined('access_group_' . $group . '_title')) {
            $OSCOM_Language->loadIniFile( 'modules/access/groups/' . $group . '.php' );
        }

        return $OSCOM_Language->get('access_group_' . $group . '_title');
    }

    public static function hasAccess(string $site, string $application): bool
    {
        $guest_applications = [];

        $callable = [
            'osCommerce\\OM\\Core\\Site\\' . $site . '\\Controller',
            'getGuestApplications'
        ];

        if (is_callable($callable)) {
            $guest_applications = call_user_func($callable);
        }

        return in_array($application, $guest_applications) || isset($_SESSION[$site]['access'][$application]);
    }
}
