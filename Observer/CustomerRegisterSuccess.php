<?php

/**
 * Lootly_Lootly extension
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Observer;

class CustomerRegisterSuccess implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Lootly\Lootly\Helper\Data $orderNotifierHelper
     * @param \Lootly\Lootly\Helper\Api $orderNotifierApiHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Lootly\Lootly\Helper\Data $orderNotifierHelper,
        \Lootly\Lootly\Helper\Api $orderNotifierApiHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->orderNotifierHelper = $orderNotifierHelper;
        $this->orderNotifierApiHelper = $orderNotifierApiHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * On customer register
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $storeId = $this->storeManager->getStore()->getId();
        $customer = $observer->getData('customer');
        $_helperApi = $this->orderNotifierApiHelper;
        $_helper = $this->orderNotifierHelper;
        $customerEmail = $customer->getEmail();
        $customerId = $customer->getId();
        $apiData = [
            'id' => $customerId,
            'email' => $customerEmail,
            "first_name" => $customer->getFirstname(),
            "last_name" => $customer->getLastname(),
            'state' => 'enabled',
            "key" => $_helper->getApiKey($storeId),
        ];
        $customerDob = $customer->getDob();
        if ($customerDob && $customerDob != '0000-00-00 00:00:00' && $customerDob != '0000-00-00') {
            $apiData['birthday'] = $customerDob;
        }
        $secretKey = $_helper->getApiSecret($storeId);

        ksort($apiData);
        $apiData["hmac"] = base64_encode(hash_hmac('sha256', json_encode($apiData), $secretKey, true));
        $url = $_helperApi->getNewCustomerURL();
        $result = $_helperApi->call($apiData, $url);

        return $this;
    }
}
