<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Scripts\CACert;

use osCommerce\OM\Core\{
    HttpRequest,
    OSCOM,
    RunScript
};

class Download implements \osCommerce\OM\Core\RunScriptInterface
{
    public static function execute()
    {
        $pem_contents = HttpRequest::getResponse([
            'url' => 'https://curl.haxx.se/ca/cacert.pem'
        ]);

        if ((mb_strlen($pem_contents) > 1000) && (mb_strpos(mb_substr($pem_contents, 0, 1000), 'Certificate data from Mozilla as of') !== false)) {
            if (!is_file(OSCOM::BASE_DIRECTORY . 'External/cacert.pem') || (sha1_file(OSCOM::BASE_DIRECTORY . 'External/cacert.pem') !== sha1($pem_contents))) {
                if (file_put_contents(OSCOM::BASE_DIRECTORY . 'External/cacert.pem', $pem_contents, LOCK_EX) !== false) {
                    RunScript::error('Updated Mozilla Certificate Data: ' . OSCOM::BASE_DIRECTORY . 'External/cacert.pem');
                } else {
                    RunScript::error('Could not update Mozilla Certificate Data: ' . OSCOM::BASE_DIRECTORY . 'External/cacert.pem');
                }
            }
        }
    }
}
