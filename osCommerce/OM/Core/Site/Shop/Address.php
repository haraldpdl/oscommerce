<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2011 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Shop;

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

/**
 * The Address class handles address related functions such as the format and country and zone information
 */

  class Address {

/**
 * Correctly format an address to the address format rule assigned to its country
 *
 * @param array $address An array (or address_book ID) containing the address information
 * @param string $new_line The string to break new lines with
 * @access public
 * @return string
 */

    public static function format($address, $new_line = null) {
      $OSCOM_PDO = Registry::get('PDO');

      $address_format = '';

      if ( is_numeric($address) ) {
        $Qaddress = $OSCOM_PDO->prepare('select ab.entry_firstname as firstname, ab.entry_lastname as lastname, ab.entry_company as company, ab.entry_street_address as street_address, ab.entry_suburb as suburb, ab.entry_city as city, ab.entry_postcode as postcode, ab.entry_state as state, ab.entry_zone_id as zone_id, ab.entry_country_id as country_id, z.zone_code as zone_code, c.countries_name as country_title from :table_address_book ab left join :table_zones z on (ab.entry_zone_id = z.zone_id), :table_countries c where ab.address_book_id = :address_book_id and ab.entry_country_id = c.countries_id');
        $Qaddress->bindInt(':address_book_id', $address);
        $Qaddress->execute();

        $address = $Qaddress->fetch();
      }

      $firstname = $lastname = '';

      if ( isset($address['firstname']) && !empty($address['firstname']) ) {
        $firstname = $address['firstname'];
        $lastname = $address['lastname'];
      } elseif ( isset($address['name']) && !empty($address['name']) ) {
        $firstname = $address['name'];
      }

      $state = $address['state'];
      $state_code = $address['zone_code'] ?? null;

      if ( isset($address['zone_id']) && is_numeric($address['zone_id']) && ($address['zone_id'] > 0) ) {
        $state = self::getZoneName($address['zone_id']);
        $state_code = self::getZoneCode($address['zone_id']);
      } elseif (empty($state) && !empty($state_code) && isset($address['country_id'])) {
        $zone_id = self::getZoneId($address['country_id'], $state_code);

        if ($zone_id !== -1) {
          $state = self::getZoneName($zone_id);
        }
      }

      $country = $address['country_title'] ?? null;

      if ( empty($country) && isset($address['country_id']) && is_numeric($address['country_id']) && ($address['country_id'] > 0) ) {
        $country = self::getCountryName($address['country_id']);
      }

      if ( isset($address['format']) ) {
        $address_format = $address['format'];
      } elseif ( isset($address['country_id']) && is_numeric($address['country_id']) && ($address['country_id'] > 0) ) {
        $address_format = self::getFormat($address['country_id']);
      }

      if ( empty($address_format) ) {
        $address_format = ":company\n:name\n:street_address\n:street_address_2\n:suburb\n:postcode :city\n:state\n:country";
      }

      $find_array = array('/\:company\b/',
                          '/\:name\b/',
                          '/\:street_address\b/',
                          '/\:street_address_2\b/',
                          '/\:suburb\b/',
                          '/\:city\b/',
                          '/\:postcode\b/',
                          '/\:state\b/',
                          '/\:state_code\b/',
                          '/\:country\b/',
                          '/' . "\n\n" . '+/s');

      $replace_array = array(HTML::outputProtected($address['company'] ?? ''),
                             HTML::outputProtected($firstname . ' ' . $lastname),
                             HTML::outputProtected($address['street_address']),
                             HTML::outputProtected($address['street_address_2'] ?? ''),
                             HTML::outputProtected($address['suburb'] ?? ''),
                             HTML::outputProtected($address['city']),
                             HTML::outputProtected($address['postcode']),
                             HTML::outputProtected($state),
                             HTML::outputProtected($state_code),
                             HTML::outputProtected($country),
                             "\n");

      $formated = trim(preg_replace($find_array, $replace_array, $address_format));

      if ( !empty($new_line) ) {
        $formated = str_replace("\n", $new_line, $formated);
      }

      return $formated;
    }

/**
 * Return all countries in an array
 *
 * @access public
 * @return array
 */

    public static function getCountries() {
      $OSCOM_PDO = Registry::get('PDO');

      static $countries;

      if ( !isset($countries) ) {
        $countries = array();

        $Qcountries = $OSCOM_PDO->query('select * from :table_countries order by countries_name');
        $Qcountries->execute();

        while ( $Qcountries->fetch() ) {
          $countries[] = array('id' => $Qcountries->valueInt('countries_id'),
                               'name' => $Qcountries->value('countries_name'),
                               'iso_2' => $Qcountries->value('countries_iso_code_2'),
                               'iso_3' => $Qcountries->value('countries_iso_code_3'),
                               'format' => $Qcountries->value('address_format'));
        }
      }

      return $countries;
    }

    public static function countryExists($id) {
      $OSCOM_PDO = Registry::get('PDO');

      if (!is_numeric($id) && !in_array(strlen($id), [2, 3])) {
        return false;
      }

      $sql = 'select countries_id from :table_countries where ';

      if (is_numeric($id)) {
        $sql .= 'countries_id = :countries_id';
      } elseif (strlen($id) === 2) {
        $sql .= 'countries_iso_code_2 = :countries_iso_code_2';
      } else {
        $sql .= 'countries_iso_code_3 = :countries_iso_code_3';
      }

      $sql .= ' limit 1';

      $Qcountry = $OSCOM_PDO->prepare($sql);

      if (is_numeric($id)) {
        $Qcountry->bindInt(':countries_id', $id);
      } elseif (strlen($id) === 2) {
        $Qcountry->bindValue(':countries_iso_code_2', $id);
      } else {
        $Qcountry->bindValue(':countries_iso_code_3', $id);
      }

      $Qcountry->execute();

      return $Qcountry->fetch() !== false;
    }

    public static function getCountryId($code) {
      $OSCOM_PDO = Registry::get('PDO');

      if (in_array(strlen($code), [2, 3])) {
        $sql = 'select countries_id from :table_countries where ';

        if (strlen($code) === 2) {
          $sql .= 'countries_iso_code_2 = :countries_iso_code_2';
        } else {
          $sql .= 'countries_iso_code_3 = :countries_iso_code_3';
        }

        $sql .= ' limit 1';

        $Qcountry = $OSCOM_PDO->prepare($sql);

        if (strlen($code) === 2) {
          $Qcountry->bindValue(':countries_iso_code_2', $code);
        } else {
          $Qcountry->bindValue(':countries_iso_code_3', $code);
        }

        $Qcountry->execute();

        if ($Qcountry->fetch() !== false) {
          return $Qcountry->valueInt('countries_id');
        }
      }

      return -1;
    }

/**
 * Return the country name
 *
 * @param int $id The ID of the country
 * @access public
 * @return string
 */

    public static function getCountryName($id) {
      $OSCOM_PDO = Registry::get('PDO');

      $Qcountry = $OSCOM_PDO->prepare('select countries_name from :table_countries where countries_id = :countries_id');
      $Qcountry->bindInt(':countries_id', $id);
      $Qcountry->execute();

      return $Qcountry->value('countries_name');
    }

/**
 * Return the country 2 character ISO code
 *
 * @param int $id The ID of the country
 * @access public
 * @return string
 */

    public static function getCountryIsoCode2($id) {
      $OSCOM_PDO = Registry::get('PDO');

      $Qcountry = $OSCOM_PDO->prepare('select countries_iso_code_2 from :table_countries where countries_id = :countries_id');
      $Qcountry->bindInt(':countries_id', $id);
      $Qcountry->execute();

      return $Qcountry->value('countries_iso_code_2');
    }

/**
 * Return the country 3 character ISO code
 *
 * @param int $id The ID of the country
 * @access public
 * @return string
 */

    public static function getCountryIsoCode3($id) {
      $OSCOM_PDO = Registry::get('PDO');

      $Qcountry = $OSCOM_PDO->prepare('select countries_iso_code_3 from :table_countries where countries_id = :countries_id');
      $Qcountry->bindInt(':countries_id', $id);
      $Qcountry->execute();

      return $Qcountry->value('countries_iso_code_3');
    }

/**
 * Return the address format rule for the country
 *
 * @param int $id The ID of the country
 * @access public
 * @return string
 */

    public static function getFormat($id) {
      $OSCOM_PDO = Registry::get('PDO');

      $Qcountry = $OSCOM_PDO->prepare('select address_format from :table_countries where countries_id = :countries_id');
      $Qcountry->bindInt(':countries_id', $id);
      $Qcountry->execute();

      return $Qcountry->value('address_format');
    }

    public static function getVatIdTitleAbr($id) {
      $OSCOM_PDO = Registry::get('PDO');

      $Qcountry = $OSCOM_PDO->prepare('select vatid_title_abr from :table_countries where countries_id = :countries_id');
      $Qcountry->bindInt(':countries_id', $id);
      $Qcountry->execute();

      return $Qcountry->value('vatid_title_abr');
    }

    public static function getZoneId(int $country_id, string $zone_code) {
      $OSCOM_PDO = Registry::get('PDO');

      $Qzone = $OSCOM_PDO->prepare('select zone_id from :table_zones where zone_country_id = :zone_country_id and zone_code = :zone_code');
      $Qzone->bindInt(':zone_country_id', $country_id);
      $Qzone->bindValue(':zone_code', $zone_code);
      $Qzone->execute();

      if ($Qzone->fetch() !== false) {
        return $Qzone->valueInt('zone_id');
      }

      return -1;
    }

/**
 * Return the zone name
 *
 * @param int $id The ID of the zone
 * @access public
 * @return string
 */

    public static function getZoneName($id) {
      $OSCOM_PDO = Registry::get('PDO');

      $Qzone = $OSCOM_PDO->prepare('select zone_name from :table_zones where zone_id = :zone_id');
      $Qzone->bindInt(':zone_id', $id);
      $Qzone->execute();

      return $Qzone->value('zone_name');
    }

/**
 * Return the zone code
 *
 * @param int $id The ID of the zone
 * @access public
 * @return string
 */

    public static function getZoneCode($id) {
      $OSCOM_PDO = Registry::get('PDO');

      $Qzone = $OSCOM_PDO->prepare('select zone_code from :table_zones where zone_id = :zone_id');
      $Qzone->bindInt(':zone_id', $id);
      $Qzone->execute();

      return $Qzone->value('zone_code');
    }

/**
 * Check if a country has zones
 *
 * @param int $id The ID of the country
 * @access public
 * @return boolean
 * @since v3.0.2
 */

    public static function hasZones($id) {
      $OSCOM_PDO = Registry::get('PDO');

      $Qzones = $OSCOM_PDO->prepare('select zone_id from :table_zones where zone_country_id = :zone_country_id limit 1');
      $Qzones->bindInt(':zone_country_id', $id);
      $Qzones->execute();

      return ( $Qzones->fetch() !== false );
    }

/**
 * Return the zones belonging to a country, or all zones
 *
 * @param int $id The ID of the country
 * @access public
 * @return array
 */

    public static function getZones($id = null) {
      $OSCOM_PDO = Registry::get('PDO');

      $zones_array = array();

      $sql_query = 'select z.zone_id, z.zone_country_id, z.zone_name, z.zone_code, c.countries_name from :table_zones z, :table_countries c where z.zone_country_id = c.countries_id';

      if ( !empty($id) ) {
        if ( is_array($id) ) {
          $sql_query .= ' and c.countries_iso_code_2 in (';

          for ( $i = 0, $n = count($id); $i < $n; $i++ ) {
            $sql_query .= ':iso2_' . $i . ', ';
          }

          $sql_query = substr($sql_query, 0, -2) . ')';
        } elseif ( is_numeric($id) ) {
          $sql_query .= ' and c.countries_id = :countries_id';
        } else {
          $sql_query .= ' and c.countries_iso_code_2 = :countries_iso_code_2';
        }
      }

      $sql_query .= ' order by c.countries_name, z.zone_name';

      if ( !empty($id) ) {
        $Qzones = $OSCOM_PDO->prepare($sql_query);

        if ( is_array($id) ) {
          for ( $i = 0, $n = count($id); $i < $n; $i++ ) {
            $Qzones->bindValue(':iso2_' . $i, $id[$i]);
          }
        } elseif ( is_numeric($id) ) {
          $Qzones->bindInt(':countries_id', $id);
        } else {
          $Qzones->bindValue(':countries_iso_code_2', $id);
        }
      } else {
        $Qzones = $OSCOM_PDO->query($sql_query);
      }

      $Qzones->execute();

      while ( $Qzones->fetch() ) {
        $zones_array[] = array('id' => $Qzones->valueInt('zone_id'),
                               'name' => $Qzones->value('zone_name'),
                               'code' => $Qzones->value('zone_code'),
                               'country_id' => $Qzones->valueInt('zone_country_id'),
                               'country_name' => $Qzones->value('countries_name'));
      }

      return $zones_array;
    }
  }
?>
