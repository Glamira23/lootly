<?php

namespace Lootly\Lootly\Plugin\SalesRule\Model\Rule\Condition;

/**
 * Address rule condition data model.
 */
class Address
{
    /**
     * Load attribute options
     *
     * @param mixed $address
     * @param mixed $result
     * @return $this
     */
    public function afterLoadAttributeOptions($address, $result)
    {
        $attributes = $result->getAttributeOption();
        $attributes = array_merge($attributes, ['customer_id' => __('Customer ID')]);

        $result->setAttributeOption($attributes);

        return $result;
    }
}
