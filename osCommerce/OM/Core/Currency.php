<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

class Currency
{
    /** @var array */
    protected $currencies;

    /** @var string */
    protected $default;

    /** @var string */
    protected $selected;

    public function __construct(array $currencies = null)
    {
        if (!isset($currencies)) {
            $currencies = static::load();
        }

        foreach ($currencies as $c) {
            $this->currencies[$c['code']] = [
                'id' => (int)$c['id'],
                'title' => $c['title'],
                'symbol_left' => $c['symbol_left'],
                'symbol_right' => $c['symbol_right'],
                'decimal_places' => (int)$c['decimal_places'],
                'value' => (float)$c['value'],
                'surcharge' => (float)$c['surcharge']
            ];

            if (!isset($this->default) && ((float)$c['value'] === 1.0)) {
                $this->default = $c['code'];
            }
        }
    }

    public static function load(): array
    {
        $OSCOM_PDO = Registry::get('PDO');

        return $OSCOM_PDO->call('Get');
    }

    public function show(float $number, string $currency_code = null, float $currency_value = null, bool $calculate = true): string
    {
        if (!isset($currency_code)) {
            $currency_code = $this->getDefault();
        }

        $value = $this->raw($number, $currency_code, $currency_value, $calculate, true);

        return $this->currencies[$currency_code]['symbol_left'] . $value . $this->currencies[$currency_code]['symbol_right'];
    }

    public function raw(float $number, string $currency_code = null, float $currency_value = null, bool $calculate = true, bool $use_locale = false): string
    {
        if (!isset($currency_code)) {
            $currency_code = $this->getDefault();
        }

        if ($calculate === true) {
            if (!isset($currency_value)) {
                $currency_value = $this->currencies[$currency_code]['value'];
            }

            if ($this->currencies[$currency_code]['surcharge'] > 0) {
                $currency_value += ($currency_value * $this->currencies[$currency_code]['surcharge']);
            }
        } else {
            $currency_value = 1;
        }

        $dec_point = '.';
        $thousands_sep = '';

        if ($use_locale === true) {
            $OSCOM_Language = Registry::get('Language');

            $dec_point = $OSCOM_Language->getNumericDecimalSeparator();
            $thousands_sep = $OSCOM_Language->getNumericThousandsSeparator();
        }

        $value = number_format(round($number * $currency_value, $this->currencies[$currency_code]['decimal_places']), $this->currencies[$currency_code]['decimal_places'], $dec_point, $thousands_sep);

        return $value;
    }

    public function trim(string $number, string $currency_code = null, bool $use_locale = true): string
    {
        if (!isset($currency_code)) {
            $currency_code = $this->getDefault();
        }

        $dec_point = '.';

        if ($use_locale === true) {
            $OSCOM_Language = Registry::get('Language');

            $dec_point = $OSCOM_Language->getNumericDecimalSeparator();
        }

        $number = str_replace($dec_point . str_repeat('0', $this->currencies[$currency_code]['decimal_places']), '', $number);

        return $number;
    }

    /** @return array|string */
    public function get(string $key = null, string $currency_code = null)
    {
        if (!isset($currency_code)) {
            $currency_code = $this->getDefault();
        }

        if (isset($key)) {
            return $this->currencies[$currency_code][$key];
        }

        return $this->currencies[$currency_code];
    }

    public function getCode(int $id): ?string
    {
        foreach ($this->currencies as $code => $c) {
            if ($c['id'] === $id) {
                return $code;
            }
        }

        return null;
    }

    public function getAll(): array
    {
        $result = [];

        foreach ($this->currencies as $code => $c) {
            $result[] = [
                'code' => $code,
                'title' => $c['title']
            ];
        }

        return $result;
    }

    public function showAll(float $number, bool $use_trim = false): array
    {
        $result = [];

        foreach (array_keys($this->currencies) as $code) {
            $value = $this->show($number, $code);

            if ($use_trim === true) {
                $value = $this->trim($value);
            }

            $result[$code] = $value;
        }

        return $result;
    }

    public function getDefault(bool $true_default = false): ?string
    {
        return (($true_default === false) && $this->hasSelected()) ? $this->selected : $this->default;
    }

    public function getSelected(): ?string
    {
        return $this->selected;
    }

    public function hasSelected(): bool
    {
        return isset($this->selected);
    }

    public function setSelected(string $code): bool
    {
        if ($this->exists($code)) {
            $this->selected = $code;

            return true;
        }

        return false;
    }

    public function exists(string $code): bool
    {
        return array_key_exists($code, $this->currencies);
    }
}
