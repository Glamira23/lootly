<?php
/**
 * Lootly_Lootly extension
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Model\ResourceModel\Entry;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Lootly\Lootly\Model\Entry::class,
            \Lootly\Lootly\Model\ResourceModel\Entry::class
        );
    }
}
