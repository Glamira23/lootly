<?php

/**
 * Lootly_Lootly Verfication Controller
 *
 * @category    Lootly Extensions
 * @package     Lootly_Lootly
 * @author      Lootly Inc
 */

namespace Lootly\Lootly\Controller\Create;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Checkout\Model\Cart;

class Redeem extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\ActionInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Lootly\Lootly\Helper\Api
     */
    protected $api;
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var array $requestData
     */
    protected $requestData;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $cartRepository;
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    protected $ruleRepository;
    /**
     * @var \Magento\SalesRule\Model\Converter\ToDataModel
     */
    protected $toDataModelConverter;
    /**
     * @var \Magento\SalesRule\Model\Converter\ToModel
     */
    protected $toModelConverter;
    protected $moduleList;
    protected $productCollectionFactory;
    protected $customerSession;
    protected $cart;

    public function __construct(
        Context                                          $context,
        \Lootly\Lootly\Helper\Api                        $api,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Psr\Log\LoggerInterface                         $logger,
        CartRepositoryInterface                          $cartRepository,
        \Magento\Quote\Api\CartRepositoryInterface       $quoteRepository,
        Session                                          $customerSession,
        Cart                                             $cart
    )
    {
        $this->api = $api;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->cartRepository = $cartRepository;
        $this->quoteRepository = $quoteRepository;
        $this->customerSession = $customerSession;
        $this->cart = $cart;
        parent::__construct($context);
    }

    /**
     * Default action
     */
    public function execute()
    {
        $postdata = $this->_getRequestData();
        try {
            if (isset($postdata['reward'])) {
                $couponCode = $this->_createRedeem($postdata['reward']);
                $data = $this->applyCoupon($couponCode);
            }
        } catch (\Exception $e) {
            $data = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->error($e->getMessage());
        }
        $result = $this->resultJsonFactory->create();
        $result->setData($data);
        return $result;
    }

    /**
     * _isAllowed
     *
     * @access protected
     * @return true
     */
    protected function _isAllowed()
    {
        return true;
    }

    protected function _createRedeem($rewardId)
    {
        $customer = $this->customerSession->getCustomer();
        if ($customer) {
            $email = $customer->getEmail();
            if ($email) {
                $result = $this->api->redeemReward($rewardId, $email);
                if ($result && isset($result['data']) && isset($result['data']['data']) && isset($result['data']['data']['coupon_code'])) {
                    $couponCode = $result['data']['data']['coupon_code'];
                    return $couponCode;
                }
            }
        }
        return false;
    }


    /**
     * Get Request data
     *
     * @return array|mixed
     */
    protected function _getRequestData()
    {
        if (!$this->requestData) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $data = file_get_contents('php://input');
                $this->requestData = json_decode($data, true);
            } else {
                $this->requestData = $this->getRequest()->getParams();
            }
        }
        return $this->requestData;
    }

    protected function applyCoupon($couponCode)
    {
        if ($couponCode) {
            $quote = $this->cart->getQuote();
            $quote->setCouponCode($couponCode)->collectTotals();
            $quote->save();
            if ($quote->getCouponCode() === $couponCode) {
                $data = [
                    'success' => true
                ];
            } else {
                $data = [
                    'success' => false,
                    'message' => __('Coupon not applied.') . ' ' . $couponCode
                ];
            }
        } else {
            $data = ['success' => false];
        }
        return $data;
    }
}
