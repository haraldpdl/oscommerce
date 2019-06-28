<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Scripts;

use osCommerce\OM\Core\{
    DirectoryListing,
    HttpRequest,
    OSCOM,
    RunScript
};

class Schedule implements \osCommerce\OM\Core\RunScriptInterface
{
    public static function execute()
    {
        $task = RunScript::getOpt('task');

        if (isset($task)) {
            static::runTask($task);
        } else {
            static::runTasks();
        }
    }

    protected static function runTask(string $task)
    {
        try {
            foreach (explode('\\', $task) as $t) {
                if (!OSCOM::isValidClassName($t)) {
                    throw new \Exception('Invalid task class name: ' . $task);
                }
            }

            if (!class_exists($task)) {
                throw new \Exception('Task class file not found: ' . $task);
            }

            if (!is_subclass_of($task, 'osCommerce\\OM\\Core\\RunScriptInterface')) {
                throw new \Exception('Task class (' . $task . ') does not implement osCommerce\\OM\\Core\\RunScriptInterface');
            }

            $callable = [
                $task,
                'execute'
            ];

            if (!is_callable($callable)) {
                throw new \Exception('Cannot execute task class (' . $task . ')');
            }

            define('OSCOM\\SCRIPT_SCHEDULE_TASK_LOCKFILE', OSCOM::BASE_DIRECTORY . 'Work/Temp/schedule-' . sha1($task) . '.lockfile');

            if (is_file(\OSCOM\SCRIPT_SCHEDULE_TASK_LOCKFILE)) {
                throw new \Exception('Task lockfile already exists (' . $task . ') at: ' . \OSCOM\SCRIPT_SCHEDULE_TASK_LOCKFILE);
            }

            if (file_put_contents(\OSCOM\SCRIPT_SCHEDULE_TASK_LOCKFILE, $task) === false) {
                throw new \Exception('Task lockfile cannot be created (' . $task . ') at: ' . \OSCOM\SCRIPT_SCHEDULE_TASK_LOCKFILE);
            }

            set_time_limit(0);

            call_user_func($callable);

            unlink(\OSCOM\SCRIPT_SCHEDULE_TASK_LOCKFILE);
        } catch (\Exception $e) {
            RunScript::error('(Schedule::runTask) ' . $e->getMessage());
        }
    }

    protected static function runTasks()
    {
        $tasks = [];

        // directory => namespace
        $dirs = [
            'Scripts' => 'Scripts'
        ];

        $base_dirs = [
            'Core',
            'Custom'
        ];

        foreach ($base_dirs as $base_dir) {
            $DL = new DirectoryListing(OSCOM::BASE_DIRECTORY . $base_dir . '/Site');
            $DL->setIncludeFiles(false);

            foreach ($DL->getFiles() as $site) {
                $script_dir = $DL->getDirectory() . '/' . $site['name'] . '/' . 'Scripts';

                if (is_dir($script_dir)) {
                    $dirs[$base_dir . '/Site/' . $site['name'] . '/Scripts'] = 'Core\\Site\\' . $site['name'] . '\\Scripts';
                }
            }
        }

        foreach ($dirs as $dir => $namespace) {
            $DL_Scripts = new DirectoryListing(OSCOM::BASE_DIRECTORY . $dir);
            $DL_Scripts->setIncludeFiles(false);

            foreach ($DL_Scripts->getFiles() as $script) {
                $schedule_file = $DL_Scripts->getDirectory() . '/' . $script['name'] . '/schedule.txt';

                if (is_file($schedule_file)) {
                    $jobs = file($schedule_file);

                    if ($jobs !== false) {
                        foreach ($jobs as $job) {
                            try {
                                $job = trim($job);

                                if (!empty($job)) {
                                    if (mb_strpos($job, ';') !== false) {
                                        [$schedule, $command] = explode(';', $job, 2);

                                        $schedule = trim($schedule);
                                        $command = trim($command);

                                        foreach (explode('\\', $command) as $c) {
                                            if (!OSCOM::isValidClassName($c)) {
                                                throw new \Exception('Invalid class name (' . $command . ') in: ' . $schedule_file);
                                            }
                                        }

                                        $command_class = 'osCommerce\\OM\\' . $namespace . '\\' . $script['name'] . '\\' . $command;

                                        // custom classes are automatically executed so skip to avoid duplicates when $base_dir is 'Custom'
                                        if (in_array($command_class, $tasks)) {
                                            continue;
                                        }

                                        if (\Cron\CronExpression::isValidExpression($schedule)) {
                                            $cron = \Cron\CronExpression::factory($schedule);

                                            if ($cron->isDue()) {
                                                if (class_exists($command_class)) {
                                                    if (is_subclass_of($command_class, 'osCommerce\\OM\\Core\\RunScriptInterface')) {
                                                        $tasks[] = $command_class;
                                                    } else {
                                                        throw new \Exception('Task class (' . $job . ') does not implement osCommerce\\OM\\Core\\RunScriptInterface in: ' . $schedule_file);
                                                    }
                                                } else {
                                                    throw new \Exception('Task class file not found (' . $job . ') in: ' . $schedule_file);
                                                }
                                            }
                                        } else {
                                            throw new \Exception('Invalid schedule expression (' . $job . ') in: ' . $schedule_file);
                                        }
                                    } else {
                                        throw new \Exception('Invalid script schedule file: ' . $schedule_file);
                                    }
                                }
                            } catch (\Exception $e) {
                                RunScript::error('(Schedule::runTasks) ' . $e->getMessage());
                            }
                        }
                    }
                }
            }
        }

        set_time_limit(0);

        foreach ($tasks as $task) {
            if (PHP_SAPI === 'cli') {
                passthru(RunScript::$php_binary . ' ' . escapeshellarg(OSCOM::PUBLIC_DIRECTORY . 'index.php') . ' --script=Schedule --task=' . escapeshellarg($task) . (RunScript::$override_offline ? ' --override-offline' : ''));
            } elseif (isset($_GET['RunScript'])) {
                echo HttpRequest::getResponse([
                    'url' => OSCOM::getLink(null, null, 'RunScript=Schedule&task=' . $task . (RunScript::$override_offline ? '&override-offline' : ''), 'SSL', false),
                    'parameters' => 'key=' . OSCOM::getConfig('runscript_key', 'OSCOM')
                ]);
            }
        }
    }
}
