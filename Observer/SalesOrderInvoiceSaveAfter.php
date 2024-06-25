<?php

/**
 * Lootly_Lootly extension
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Observer;

class SalesOrderInvoiceSaveAfter implements \Magento\Framework\Event\ObserverInterface
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
     * On invoice create
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $invoiceId = $observer->getEvent()->getInvoice()->getOrigData('entity_id');
        if ($invoiceId === null) {
            $order = $observer->getEvent()->getInvoice()->getOrder();
            $storeId = $order->getStoreId();
            $targetStatus = $this->orderNotifierApiHelper->getTargetOrderStatus($storeId);
            if ($targetStatus != '' && $targetStatus == 'invoiced') {
                $this->orderNotifierApiHelper->sendToApi($order, $targetStatus);
            }
        }

        return $this;
    }
}
