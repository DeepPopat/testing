<?php
/**
 * Copyright © Emipro Technologies Pvt Ltd. All rights reserved.
 * @license http://shop.emiprotechnologies.com/license-agreement/
 */

namespace Emipro\Smartproductselector\Model\Template\Rule\Action;

class Collection extends \Magento\Rule\Model\Action\Collection
{
    public function getNewChildSelectOptions()
    {
        $actions = parent::getNewChildSelectOptions();
        $actions = array_merge_recursive(
            $actions,
            [

            ]
        );

        return $actions;
    }
}
