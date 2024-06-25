<?php

/**
 * Lootly_Lootly extension
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Observer;

class Refund implements \Magento\Framework\Event\ObserverInterface
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
     * On refund
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $observer->getData('creditmemo');
        /** @var \Magento\Sales\Model\Order $order */
        $order = $creditmemo->getOrder();
        $refundTotal = $creditmemo->getGrandTotal();
        $productsData = [];
        foreach ($creditmemo->getAllItems() as $item) {
            $productData = [];
            $productData['product_id'] = $item->getProductId();
            $productData['product_name'] = $item->getName();
            $productData['product_price'] = $item->getBasePrice();
            $productData['quantity'] = $item->getQty();
            $productsData[] = $productData;
        }
        $apiData = [
            'id' => $order->getIncrementId(),
            'transactions' => [
                [
                    'amount' => $refundTotal
                ]
            ],
            'products' => $productsData,
            "key" => $this->orderNotifierApiHelper->getApiKey(),
        ];

        $secretKey = $this->orderNotifierApiHelper->getApiSecret();

        ksort($apiData);
        $apiData["hmac"] = base64_encode(hash_hmac('sha256', json_encode($apiData), $secretKey, true));

        $url = $this->orderNotifierApiHelper->getRefundURL();
        $result = $this->orderNotifierApiHelper->call($apiData, $url);
    }
}
