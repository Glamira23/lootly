<?php
/**
 * Lootly_Lootly extension
 *
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2019
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Model\Order\Status;

/**
 * OrderNotifier setup
 *
 */

class Refund
{
    /**
     * Convert to options array
     *
     * @access public
     * @return array
     * @author Ultimate Module Creator
     */
    public function toOptionArray()
    {
        $statuses = [
            '' => __('-- Please Select --'),
            'refund' => __('Refund'),
            'canceled' => __('Canceled'),
        ];
        return $statuses;
    }
}
