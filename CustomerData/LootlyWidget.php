<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Lootly\Lootly\CustomerData;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;

/**
 * Customer section
 */
class LootlyWidget implements \Magento\Customer\CustomerData\SectionSourceInterface
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var View
     */
    private $customerViewHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param View $customerViewHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        View $customerViewHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->customerViewHelper = $customerViewHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get Section Data
     */
    public function getSectionData()
    {
        if (!$this->currentCustomer->getCustomerId()) {
            return ['customerId' => '', 'customerSignature' => ''];
        }
        $customer = $this->currentCustomer->getCustomer();
        $customerId = $customer->getId();
        return $this->getSectionDataByCustomerId($customerId);
    }

    /**
     * Get Section Data by customer id
     *
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSectionDataByCustomerId($customerId)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $scope = 'default';
        if ($storeId) {
            $scope = 'store';
        }
        $secret = $this->_scopeConfig->getValue('lootlyordernotifier/general/api_secret', $scope, $storeId);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $MD5Source = $objectManager->get('\Lootly\Lootly\Helper\MD5Source');
        $customerSignature = $MD5Source->md($customerId . $secret);

        return [
            'customerId' => $customerId,
            'customerSignature' => $customerSignature,
        ];
    }
}
