<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

use osCommerce\OM\Core\{
    OSCOM,
    RunScript
};

define('OSCOM\\PUBLIC_BASE_DIRECTORY', __DIR__ . '/');

require('osCommerce/OM/bootstrap.php');

if ((PHP_SAPI === 'cli') || (isset($_GET['RunScript']) && !empty($_GET['RunScript']))) {
    RunScript::execute();
}

OSCOM::initialize();

echo $OSCOM_Template->getContent();
