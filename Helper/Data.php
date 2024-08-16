<?php

/**
 * Lootly_Lootly extension
 *
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2019
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Lootly_Lootly helper
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Lootly Signup page URL
     */
    public const SIGNUP_URL = 'https://lootly.io/signup';

    /**
     * Lootly Login page URL
     */
    public const LOGIN_URL = 'https://lootly.io/login';

    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;
    /**
     * @var \Lootly\Lootly\Model\EntryFactory
     */
    protected $orderNotifierEntryFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param Context $context
     * @param ModuleListInterface $moduleList
     * @param \Lootly\Lootly\Model\EntryFactory $orderNotifierEntryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        ModuleListInterface $moduleList,
        \Lootly\Lootly\Model\EntryFactory $orderNotifierEntryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->orderNotifierEntryFactory = $orderNotifierEntryFactory;
        $this->_moduleList = $moduleList;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get config value
     *
     * @param string $code
     * @param integer|null $storeId
     * @return mixed
     */
    public function getConfigValue($code, $storeId)
    {
        $scope = 'default';
        if ($storeId > 0) {
            $scope = 'store';
        }
        return $this->scopeConfig->getValue($code, $scope, $storeId);
    }

    /**
     * Retrieve Email Address
     *
     * @access public
     * @param int|null $storeId
     * @return string
     * @author Lootly Inc
     */
    public function getEmail($storeId = null)
    {
        return $this->getConfigValue('lootlyordernotifier/general/email', $storeId);
    }

    /**
     * Get API Key
     *
     * @access public
     * @param int|null $storeId
     * @return string
     * @author Lootly Inc
     */
    public function getApiKey($storeId = null)
    {
        return $this->getConfigValue('lootlyordernotifier/general/api_key', $storeId)?:'';
    }

    /**
     * Get API Secret
     *
     * @access public
     * @param int|null $storeId
     * @return string
     * @author Lootly Inc
     */
    public function getApiSecret($storeId = null)
    {
        return $this->getConfigValue('lootlyordernotifier/general/api_secret', $storeId)?:'';
    }

    /**
     * Get Order Status
     *
     * @access public
     * @param int|null $storeId
     * @return string
     * @author Lootly Inc
     */
    public function getTargetOrderStatus($storeId = null)
    {
        return $this->getConfigValue('lootlyordernotifier/general/order_status', $storeId);
    }

    /**
     * Get Lootly Signup page URL
     *
     * @access public
     * @return string
     * @author Lootly Inc
     */
    public function getSignupURL()
    {
        return self::SIGNUP_URL;
    }

    /**
     * Get Extension version
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        $module = $this->_moduleList->getOne('Lootly_Lootly');
        return (string)$module['setup_version'];
    }

    /**
     * Get refund status
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function getRefundOrderStatus($storeId = null)
    {
        return $this->getConfigValue('lootlyordernotifier/general/refund_status', $storeId);
    }

    /**
     * Get config auto aply coupon
     *
     * @return mixed
     */
    public function getAutoApplyCoupon()
    {
        return $this->getConfigValue('lootlyordernotifier/advanced/auto_apply_coupon', null);
    }

    /**
     * Get config auto aply free product
     *
     * @return mixed
     */
    public function getAutoApplyFreeProduct()
    {
        return $this->getConfigValue('lootlyordernotifier/advanced/auto_apply_freeproduct', null);
    }
}
