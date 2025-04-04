<?php

namespace Lootly\Lootly\Block;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Cart;
/**
 * Lootly_Lootly Adminhtml Block for Verify Settings
 *
 */
class Widget extends \Magento\Framework\View\Element\Template
{
    public $api;

    protected $formKey;
    protected $cart;

    public function __construct(
        Template\Context $context,
        \Lootly\Lootly\Helper\Api $api,
        \Magento\Framework\Data\Form\FormKey $formKey,
        Cart $cart,
        array $data = []
    )
    {
        $this->api = $api;
        $this->formKey = $formKey;
        $this->cart = $cart;
        parent::__construct($context, $data);
    }

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
    public function getRewardList($storeId = null)
    {
        $rewardsListData = $this->api->getRewardsList($storeId);
        if (isset($rewardsListData['data'])) {
            return $rewardsListData['data'];
        } else {
            return [];
        }
    }
    public function getCustomerData($storeId = null){
        return $this->api->getCustomer($this->getCustomerId());
    }
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
    public function getCartAmount(){
        return $this->cart->getQuote()->getSubtotal();
    }
}
