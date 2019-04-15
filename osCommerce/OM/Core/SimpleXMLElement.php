<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core;

class SimpleXMLElement extends \SimpleXMLElement
{
    public function addChildCData($name, $value)
    {
        $child = $this->addChild($name);
        $child->addCData($value);
    }

    private function addCData($value)
    {
        $node = dom_import_simplexml($this);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($value));
    }
}
