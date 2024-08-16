<?php

/**
 * Lootly_Lootly extension
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Observer;

class SalesOrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Lootly\Lootly\Helper\Data
     */
    protected $orderNotifierHelper;

    /**
     * @var \Lootly\Lootly\Helper\Api
     */
    protected $orderNotifierApiHelper;

    /**
     * @param \Lootly\Lootly\Helper\Data $orderNotifierHelper
     * @param \Lootly\Lootly\Helper\Api $orderNotifierApiHelper
     */
    public function __construct(
        \Lootly\Lootly\Helper\Data $orderNotifierHelper,
        \Lootly\Lootly\Helper\Api $orderNotifierApiHelper
    ) {
        $this->orderNotifierHelper = $orderNotifierHelper;
        $this->orderNotifierApiHelper = $orderNotifierApiHelper;
    }

    /**
     * On order status change
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $order = $observer->getOrder();
        $_helper = $this->orderNotifierHelper;
        $storeId = $order->getStoreId();
        $targetStatus = $_helper->getTargetOrderStatus($storeId);
        $refundStatus = $_helper->getRefundOrderStatus($storeId);
        if ($targetStatus != '' && $targetStatus == $order->getState()) {
            $this->orderNotifierApiHelper->sendToApi($order, $order->getState());
        }

        if (in_array(strtolower($order->getStatus()),['refund','canceled'])) {
            $this->orderNotifierApiHelper->processCanceled($order);
        }
        if (in_array(strtolower($order->getStatus()),['refund', 'closed'])) {
            $this->orderNotifierApiHelper->processClosed($order);
        }

        return $this;
    }
}
