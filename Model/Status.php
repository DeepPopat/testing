<?php
namespace Emipro\Smartproductselector\Model;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    const ACTIVE = 1;
    const INACTIVE = 0;

    const RELATED_PRODUCTS = 0;
    const UP_SELLES = 1;
    const CROSS_SELLES = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::ACTIVE => __('Active'),
            self::INACTIVE => __('Inactive'),
        ];

        return $options;
    }

    public function toAlternativeOptionArray()
    {
        $options = [
            self::RELATED_PRODUCTS => __('Related Products'),
            self::UP_SELLES => __('Up-sells'),
            self::CROSS_SELLES => __('Cross-sells'),
        ];

        return $options;
    }
}
