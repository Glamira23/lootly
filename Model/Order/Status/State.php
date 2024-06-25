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
class State
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
            'processing' => __('Processing'),
            'invoiced' => __('Invoiced'),
            'shipped' => __('Shipped'),
            'complete' => __('Complete'),
            'custom_event' => __('Custom Event'),
        ];
        return $statuses;
    }
}
