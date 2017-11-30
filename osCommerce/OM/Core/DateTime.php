<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

class DateTime
{
    const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    protected static $locale = 'en_US';
    protected static $localeData = [];

    public static function getNow($format = null): string
    {
        if (!isset($format)) {
            $format = static::DEFAULT_FORMAT;
        }

        return date($format);
    }

    public static function getShort($date = null, $with_time = false): string
    {
        $OSCOM_Language = Registry::get('Language');

        if (!isset($date)) {
            $date = static::getNow();
        }

        $year = substr($date, 0, 4);
        $month = (int)substr($date, 5, 2);
        $day = (int)substr($date, 8, 2);
        $hour = (int)substr($date, 11, 2);
        $minute = (int)substr($date, 14, 2);
        $second = (int)substr($date, 17, 2);

        if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
            return strftime($OSCOM_Language->getDateFormatShort($with_time), mktime($hour, $minute, $second, $month, $day, $year));
        } else {
            return preg_replace('/2037/', $year, strftime($OSCOM_Language->getDateFormatShort($with_time), mktime($hour, $minute, $second, $month, $day, 2037)));
        }
    }

    public static function getLong($date = null): string
    {
        $OSCOM_Language = Registry::get('Language');

        if (!isset($date)) {
            $date = static::getNow();
        }

        $year = substr($date, 0, 4);
        $month = (int)substr($date, 5, 2);
        $day = (int)substr($date, 8, 2);
        $hour = (int)substr($date, 11, 2);
        $minute = (int)substr($date, 14, 2);
        $second = (int)substr($date, 17, 2);

        if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
            return strftime($OSCOM_Language->getDateFormatLong(), mktime($hour, $minute, $second, $month, $day, $year));
        } else {
            return preg_replace('/2037/', $year, strftime($OSCOM_Language->getDateFormatLong(), mktime($hour, $minute, $second, $month, $day, 2037)));
        }
    }

    public static function getTimestamp($date = null, $format = null): int
    {
        if (!isset($date)) {
            $date = static::getNow($format);
        }

        if (!isset($format)) {
            $format = static::DEFAULT_FORMAT;
        }

        $dt = \DateTime::createFromFormat($format, $date);
        $timestamp = $dt->getTimestamp();

        return $timestamp;
    }

    public static function fromUnixTimestamp($timestamp, $format = null): string
    {
        if (!isset($format)) {
            $format = static::DEFAULT_FORMAT;
        }

        return date($format, $timestamp);
    }

    public static function isLeapYear($year = null): bool
    {
        if (!isset($year)) {
            $year = static::getNow('Y');
        }

        if ($year % 100 == 0) {
            if ($year % 400 == 0) {
                return true;
            }
        } else {
            if (($year % 4) == 0) {
              return true;
            }
        }

        return false;
    }

    public static function validate($date_to_check, $format_string, &$date_array): bool
    {
        $separator_idx = -1;

        $separators = ['-', ' ', '/', '.'];
        $month_abbr = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
        $no_of_days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        $format_string = strtolower($format_string);

        if (strlen($date_to_check) != strlen($format_string)) {
            return false;
        }

        $size = count($separators);

        for ($i=0; $i<$size; $i++) {
            $pos_separator = strpos($date_to_check, $separators[$i]);

            if ($pos_separator != false) {
                $date_separator_idx = $i;
                break;
            }
        }

        for ($i=0; $i<$size; $i++) {
            $pos_separator = strpos($format_string, $separators[$i]);

            if ($pos_separator != false) {
                $format_separator_idx = $i;
                break;
            }
        }

        if ($date_separator_idx != $format_separator_idx) {
            return false;
        }

        if ($date_separator_idx != -1) {
            $format_string_array = explode($separators[$date_separator_idx], $format_string);

            if (count($format_string_array) != 3) {
                return false;
            }

            $date_to_check_array = explode($separators[$date_separator_idx], $date_to_check);

            if (count($date_to_check_array) != 3) {
                return false;
            }

            $size = count($format_string_array);

            for ($i=0; $i<$size; $i++) {
                if ($format_string_array[$i] == 'mm' || $format_string_array[$i] == 'mmm') $month = $date_to_check_array[$i];
                if ($format_string_array[$i] == 'dd') $day = $date_to_check_array[$i];
                if (($format_string_array[$i] == 'yyyy') || ($format_string_array[$i] == 'aaaa')) $year = $date_to_check_array[$i];
            }
        } else {
            if (strlen($format_string) == 8 || strlen($format_string) == 9) {
                $pos_month = strpos($format_string, 'mmm');

                if ($pos_month != false) {
                    $month = substr($date_to_check, $pos_month, 3);

                    $size = count($month_abbr);

                    for ($i=0; $i<$size; $i++) {
                        if ($month == $month_abbr[$i]) {
                            $month = $i;
                            break;
                        }
                    }
                } else {
                    $month = substr($date_to_check, strpos($format_string, 'mm'), 2);
                }
            } else {
                return false;
            }

            $day = substr($date_to_check, strpos($format_string, 'dd'), 2);
            $year = substr($date_to_check, strpos($format_string, 'yyyy'), 4);
        }

        if (strlen($year) != 4) {
            return false;
        }

        if (!settype($year, 'integer') || !settype($month, 'integer') || !settype($day, 'integer')) {
            return false;
        }

        if ($month > 12 || $month < 1) {
            return false;
        }

        if ($day < 1) {
            return false;
        }

        if (static::isLeapYear($year)) {
            $no_of_days[1] = 29;
        }

        if ($day > $no_of_days[$month - 1]) {
            return false;
        }

        $date_array = [$year, $month, $day];

        return true;
    }

/**
 * Set the time zone to use for dates.
 *
 * @param string $time_zone An optional time zone to set to
 * @param string $site The Site to retrieve the time zone from
 * @return boolean
 * @since v3.0.1
 */

    public static function setTimeZone($time_zone = null, $site = 'OSCOM'): bool
    {
        if (!isset($time_zone)) {
            if (OSCOM::configExists('time_zone', $site)) {
                $time_zone = OSCOM::getConfig('time_zone', $site);
            } else {
                $time_zone = date_default_timezone_get();
            }
        }

        return date_default_timezone_set($time_zone);
    }

/**
 * Return an array of available time zones.
 *
 * @return array
 * @since v3.0.1
 */

    public static function getTimeZones(): array
    {
        $result = [];

        foreach (\DateTimeZone::listIdentifiers() as $id) {
            $tz_string = str_replace('_', ' ', $id);

            $id_array = explode('/', $tz_string, 2);

            $result[$id_array[0]][$id] = isset($id_array[1]) ? $id_array[1] : $id_array[0];
        }

        return $result;
    }

    public static function getRelative(\DateTime $time, \DateTime $to = null): ?string
    {
        if (empty(static::$localeData)) {
            $locale = Registry::exists('Language') ? Registry::get('Language')->getCode() : static::$locale;

            if (file_exists(__DIR__ . '/DateTime/Locales/' . basename($locale) . '.php')) {
                static::$locale = $locale;
            }

            static::$localeData = include(__DIR__ . '/DateTime/Locales/' . static::$locale . '.php');
        }

        if (!isset($to)) {
            $to = new \DateTime();
        }

        $diff = $to->diff($time);

        $direction = $diff->format('%R');

        if ($direction === '+') {
            $seconds = $time->getTimestamp() - $to->getTimestamp();
        } else {
            $seconds = $to->getTimestamp() - $time->getTimestamp();
        }

        $result = null;

        if ($seconds < 5) {
            $result = static::$localeData['relative']['s'];
        } elseif ($seconds < 60) {
            $result = sprintf(static::$localeData['relative']['ss'], $seconds);
        } elseif ($seconds < 90) {
            $result = $result = static::$localeData['relative']['m'];
        } elseif ($seconds < 3600) {
            if ($seconds < 120) {
                $seconds = 120;
            }

            $result = $result = sprintf(static::$localeData['relative']['ms'], intval($seconds / 60));
        } elseif ($seconds < 4500) { // 1.25 hours
            $result = static::$localeData['relative']['h'];
        } elseif ($seconds < 86400) { // 24 hours
            if ($seconds < 7200) {
                $seconds = 7200;
            }

            $result = intval($seconds / (60*60)) . ' hours';
        } elseif ($seconds < 129600) { // 1.5 days
            $result = static::$localeData['relative']['d'];
        } elseif ($seconds < 604800) { // 1 week
            if ($seconds < 172800) { // 2 days
                $seconds = 172800;
            }

            $result = sprintf(static::$localeData['relative']['ds'], intval($seconds / (60*60*24)));
        } elseif ($seconds < 691200) { // 8 days
            $result = static::$localeData['relative']['w'];
        } elseif ($seconds < 2419200) { // 4 weeks
            if ($seconds < 1209600) { // 2 weeks
                $seconds = 1209600;
            }

            $result = sprintf(static::$localeData['relative']['ws'], intval($seconds / (60*60*24*7)));
        } elseif ($seconds < 3024000) { // 5 weeks
            $result = static::$localeData['relative']['M'];
        } elseif ($seconds < 28927206) { // 11 months
            if ($seconds < 5259492) { // 2 months
                $seconds = 5259492;
            }

            $result = sprintf(static::$localeData['relative']['Ms'], intval($seconds / (60*60*24*7*4)));
        } elseif ($seconds < 39446190) { // 15 months
            $result = static::$localeData['relative']['y'];
        } else {
            if ($seconds < 63113904) { // 24 months
                $seconds = 63113904;
            }

            $result = sprintf(static::$localeData['relative']['ys'], intval($seconds / (60*60*24*7*4*12)));
        }

        $result = sprintf($direction === '+' ? static::$localeData['relative']['future'] : static::$localeData['relative']['past'], $result);

        return $result;
    }
}
