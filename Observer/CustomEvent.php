<?php

/**
 * Lootly_Lootly extension
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Observer;

use Magento\Framework\Exception\NoSuchEntityException;

class CustomEvent implements \Magento\Framework\Event\ObserverInterface
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
        \Lootly\Lootly\Helper\Api  $orderNotifierApiHelper
    ) {
        $this->orderNotifierHelper = $orderNotifierHelper;
        $this->orderNotifierApiHelper = $orderNotifierApiHelper;
    }

    /**
     * Custom Event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order) {
            throw new NoSuchEntityException('missed order entity');
        }
        $storeId = $order->getStoreId();
        $targetStatus = $this->orderNotifierApiHelper->getTargetOrderStatus($storeId);
        if ($targetStatus != '' && $targetStatus == 'custom_event') {
            $this->orderNotifierApiHelper->sendToApi($order, $targetStatus);
        }

        return $this;
    }
}
