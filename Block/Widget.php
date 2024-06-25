<?php

namespace Lootly\Lootly\Block;

use Magento\Framework\View\Element\Template;

/**
 * Lootly_Lootly Adminhtml Block for Verify Settings
 *
 */
class Widget extends \Magento\Framework\View\Element\Template
{
    /**
     * GetStoreId
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get config value
     *
     * @param string $code
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfigValue($code)
    {
        $scope = 'default';
        $storeId = $this->getStoreId();
        if ($storeId > 0) {
            $scope = 'store';
        }
        return $this->_scopeConfig->getValue($code, $scope, $storeId);
    }

    /**
     * Get Api key
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getApiKey()
    {

        return $this->getConfigValue('lootlyordernotifier/general/api_key');
    }

    /**
     * Get API sercet
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getApiSecret()
    {
        return $this->getConfigValue('lootlyordernotifier/general/api_secret');
    }

    /**
     * Get customer id
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_session = $objectManager->get(\Magento\Customer\Model\Session::class);
        return $_session->getCustomerId();
    }

    /**
     * Get custoer signature
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerSignature()
    {
        $customerId = $this->getCustomerId();
        $secret = $this->getApiSecret();

        $customerSignature = $this->getHash($customerId . $secret);
        return $customerSignature;
    }

    /**
     * Get hash
     *
     * @param string $string
     * @return mixed
     */
    public function getHash($string)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $MD5Source = $objectManager->get('\Lootly\Lootly\Helper\MD5Source');
        $hash = $MD5Source->md($string);
        return $hash;
    }
}
