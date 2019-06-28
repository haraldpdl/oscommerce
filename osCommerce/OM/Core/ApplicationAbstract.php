<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

use osCommerce\OM\Core\OSCOM;

abstract class ApplicationAbstract
{
    protected $_page_contents = 'main.php';
    protected $_page_title;
    protected $_ignored_actions = [];
    protected $_actions_run = [];

    abstract protected function initialize();

    public function __construct()
    {
        $this->initialize();

        $this->runActions();
    }

    public function getPageTitle()
    {
        return $this->_page_title;
    }

    public function setPageTitle($title)
    {
        $this->_page_title = $title;
    }

    public function getPageContent()
    {
        return $this->_page_contents;
    }

    public function setPageContent($filename)
    {
        $this->_page_contents = $filename;
    }

    public function siteApplicationActionExists($action)
    {
        return class_exists('osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Action\\' . $action);
    }

    public function ignoreAction($key)
    {
        $this->_ignored_actions[] = $key;
    }

    public function runAction($actions)
    {
        if (!is_array($actions)) {
            $actions = [
                $actions
            ];
        }

        $run = [];

        foreach ($actions as $action) {
            $run[] = $action;

            if ($this->siteApplicationActionExists(implode('\\', $run))) {
                $callable = [
                    'osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Action\\' . implode('\\', $run),
                    'execute'
                ];

                if (is_callable($callable)) {
                    call_user_func($callable, $this);
                } else {
                    break;
                }
            } else {
                break;
            }
        }
    }

    public function runActions()
    {
        foreach ($this->getRequestedActions() as $action) {
            $this->_actions_run[] = $action;

            $callable = [
                'osCommerce\\OM\\Core\\Site\\' . OSCOM::getSite() . '\\Application\\' . OSCOM::getSiteApplication() . '\\Action\\' . implode('\\', $this->_actions_run),
                'execute'
            ];

            if (is_callable($callable)) {
                call_user_func($callable, $this);
            } else {
                break;
            }
        }
    }

    public function getCurrentAction()
    {
        return end($this->_actions_run);
    }

    public function getActionsRun()
    {
        return $this->_actions_run;
    }

    public function getRequestedActions()
    {
        $furious_pete = [];

// URL is built as Site&Application&Action1&Action2, Application&Action1&Action2, or Action1&Action2 so we need
// to detect where the first action is called

        foreach (array_keys($_GET) as $snack) {
            $snack = HTML::sanitize(basename($snack));

            if (OSCOM::getSite() == $snack) {
                continue;
            }

            if (OSCOM::getSiteApplication() == $snack) {
                continue;
            }

            $furious_pete[] = $snack;

            if (in_array($snack, $this->_ignored_actions) || !static::siteApplicationActionExists(implode('\\', $furious_pete))) {
                array_pop($furious_pete);

                break;
            }

        }

        return $furious_pete;
    }
}
