<?php

namespace Emipro\Smartproductselector\Model;

use Magento\Framework\DataObject;

abstract class AbstractModel extends DataObject
{
    public function __construct(
        array $data = []
    ) {
        parent::__construct($data);
    }
}
