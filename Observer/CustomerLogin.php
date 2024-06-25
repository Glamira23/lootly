<?php

/**
 * Lootly_Lootly extension
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Observer;

class CustomerLogin implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Lootly\Lootly\Helper\Data
     */
    protected $orderNotifierHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Lootly\Lootly\Helper\Api
     */
    protected $orderNotifierApiHelper;

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
        $this->storeManager = $storeManager;
        $this->orderNotifierHelper = $orderNotifierHelper;
        $this->orderNotifierApiHelper = $orderNotifierApiHelper;
    }

    /**
     * On customer login
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $observer->getData('customer');
        $customer->load($customer->getId());
        $val = $customer->getData('lootly_registered');
        $storeId = $this->storeManager->getStore()->getStoreId();
        $valData = [];
        if ($val && !is_numeric($val) && substr($val, '0', 1)=='{') {
            $val = $valData = json_decode($val, true);
            if (isset($val[$storeId])) {
                $val = $val[$storeId];
            } else {
                $val = null;
            }
        } else {
            $val = null;
        }
        if (!$val) {
            try {
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
                ;
                $url = $_helperApi->getNewCustomerURL();
                $result = $_helperApi->call($apiData, $url);

                $valData[$storeId] = 1;
                $customer->setData('lootly_registered', json_encode($valData));
                $customerResource = $customer->getResource();
                $customerResource->saveAttribute($customer, 'lootly_registered');
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
        }
        return $this;
    }
}
