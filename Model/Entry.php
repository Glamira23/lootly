<?php
/**
 * Lootly_Lootly extension
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Model;

class Entry extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Entry id
     */
    public const ENTRY_ID = 'entry_id';
    /**
     * Order Id
     */
    public const ORDER_ID = 'order_id';

    /**
     * COnsturct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Lootly\Lootly\Model\ResourceModel\Entry::class);
    }

    /**
     * Get entry_id
     *
     * @return string
     */
    public function getEntryId()
    {
        return $this->getData(self::ENTRY_ID);
    }

    /**
     * Set entry_id
     *
     * @param string $entryId
     * @return
     */
    public function setEntryId($entryId)
    {
        return $this->setData(self::ENTRY_ID, $entryId);
    }

    /**
     * Get order_id
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set order_id
     *
     * @param string $order_id
     * @return
     */
    public function setOrderId($order_id)
    {
        return $this->setData(self::ORDER_ID, $order_id);
    }
}
