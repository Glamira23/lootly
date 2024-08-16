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

class Coupon extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\ActionInterface
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

    /**
     * @param Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Lootly\Lootly\Helper\Api $api
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param CartRepositoryInterface $cartRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository
     * @param \Magento\SalesRule\Model\Converter\ToDataModel $toDataModelConverter
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory
     */
    public function __construct(
        Context                                           $context,
        \Magento\Catalog\Model\ProductFactory             $productFactory,
        \Lootly\Lootly\Helper\Api                      $api,
        \Magento\SalesRule\Model\RuleFactory              $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface        $storeManagerInterface,
        \Magento\Customer\Api\GroupRepositoryInterface    $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder      $searchCriteriaBuilder,
        \Magento\Framework\Controller\Result\JsonFactory  $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory  $categoryCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Psr\Log\LoggerInterface                          $logger,
        CartRepositoryInterface                           $cartRepository,
        \Magento\Quote\Api\CartRepositoryInterface        $quoteRepository,
        \Magento\SalesRule\Api\RuleRepositoryInterface    $ruleRepository,
        \Magento\SalesRule\Model\Converter\ToDataModel    $toDataModelConverter,
        \Magento\SalesRule\Model\Converter\ToModel        $toModelConverter,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory             $productCollectionFactory
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->productFactory = $productFactory;
        $this->api = $api;
        $this->storeManager = $storeManagerInterface;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->cartRepository = $cartRepository;
        $this->quoteRepository = $quoteRepository;
        $this->ruleRepository = $ruleRepository;
        $this->toDataModelConverter = $toDataModelConverter;
        $this->toModelConverter = $toModelConverter;
        $this->moduleList = $moduleList;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Default action
     */
    public function execute()
    {
        $postdata1 = $this->getRequest()->getParams();
        $postdata = $this->_getRequestData();
        try {
            $q = '';
            if (isset($postdata['title'])) {
                $q = $postdata['title'];
            } elseif (isset($postdata['q'])) {
                $q = $postdata['q'];
            }

            $data = [];

            if (isset($postdata1['products']) && $q) {
                $data = $this->_productRequest($q);
            } elseif (isset($postdata1['categories']) && $q) {
                $data = $this->_categoriesRequest($q);
            } elseif (isset($postdata1['price-rules'])) {
                $data = $this->_createCoupon();
            } elseif (isset($postdata1['customer']) && $postdata1['customer'] == 'info') {
                $data = $this->_customerRequest();
            } elseif (isset($postdata1['version'])) {
                $data = $this->moduleList->getOne('Lootly_Lootly');
                $data = $data['setup_version'];
            }
        } catch (\Exception $e) {
            $data = ['error'=>$e->getMessage()];
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

    /**
     * Get product request
     *
     * @param string $q
     * @return array[]|string[]
     */
    protected function _productRequest($q)
    {
        unset($this->requestData['products']);
        if (!$this->_validateHash()) {
            $result = [
                'error' => 'Invalid Hmac',
            ];
            return $result;
        }
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToFilter([
            ['attribute' => 'name', 'like' => '%' . $q . '%'],
            ['attribute' => 'entity_id', 'like' => '%' . $q . '%']
        ]);
        $collection->addAttributeToFilter('status', ['eq' => '1']);
        $collection->addAttributeToSelect('price','name');
        $products = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {
            $products[] = [
                'id' => $product->getEntityId(),
                'title' => $product->getName(),
                /*"variants" => array(
                    "id"=> $product->getEntityId(),
                    "product_id" => $product->getEntityId(),
                    'title' => $product->getName(),
                    'price' => $product->getPrice(),
                    'sku' => $product->getSku(),
                )*/
            ];
        }
        return ['products' => $products];
    }

    /**
     * Get Categories request
     *
     * @param string $q
     * @return array[]|string[]
     */
    protected function _categoriesRequest($q)
    {
        unset($this->requestData['categories']);
        if (!$this->_validateHash()) {
            $result = [
                'error' => 'Invalid Hmac',
            ];
            return $result;
        }
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToFilter([
            ['attribute' => 'name', 'like' => '%' . $q . '%'],
            ['attribute' => 'entity_id', 'like' => '%' . $q . '%']
        ]);
        $collection->addAttributeToSelect('name');
        $categories = [];
        foreach ($collection as $category) {
            $categories[] = [
                'id' => $category->getEntityId(),
                'title' => $category->getName(),
            ];
        }
        return ['categories' => $categories];
    }

    /**
     * Validate Hash
     *
     * @return bool
     */
    protected function _validateHash()
    {
        $params = $data = $this->_getRequestData();
        if (!$params) {
            return false;
        }
        unset($params['hmac']);
        ksort($params);
        $data1 = json_encode($params);
        $stores = $this->storeManager->getStores();
        $apiSecret = '';
        foreach ($stores as $store) {
            $apiKey = $this->api->getApiKey($store->getId());
            if ($apiKey == $params['key']) {
                $apiSecret = $this->api->getApiSecret($store->getId());
                break;
            }
        }
        $generatedHmac = base64_encode(hash_hmac('sha256', $data1, $apiSecret, true));
        return $generatedHmac == $data['hmac'];
    }

    /**
     * Get Request data
     *
     * @return array|mixed
     */
    protected function _getRequestData()
    {
        if (!$this->requestData) {
            if ($_SERVER['REQUEST_METHOD']=='POST') {
                $data = file_get_contents('php://input');
                $this->requestData = json_decode($data, true);
            } else {
                $this->requestData = $this->getRequest()->getParams();
            }
        }
        return $this->requestData;
    }

    /**
     * Create Coupon
     *
     * @return array|array[]|string[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _createCoupon()
    {
        $result = [];
        $data = $this->_getRequestData();
        //$this->logger->info(json_encode($data));
        if (!$this->_validateHash()) {
            $result = [
                'error' => 'Invalid Hmac',
            ];
            return $result;
        }
        /** @var \Magento\SalesRule\Model\Rule $model */
        $model = $this->ruleFactory->create();
        $priceRules = $this->getRequest()->getParam('price-rules');
        if ($priceRules) {
            $model->load($priceRules);
            $result = [
                'discount_code' => [
                    'id' => $model->getRuleId(),
                    'price_rule_id' => $model->getRuleId(),
                    'code' => $model->getCouponCode(),
                    'usage_count' => $model->getTimesUsed()
                ]
            ];
        } else {
            $dataRecived = $data['price_rule'];
            $fromDate = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime');
            $from_date = $fromDate->gmDate('Y-m-d H:i:s', strtotime($dataRecived['starts_at']));
            $to_date = new \DateTime('now+10years');
            $to_date = $to_date->format('Y-m-d H:i:s');
            $currentStoreId = $this->storeManager->getStore()->getId();
            $customRuleId = (int)$this->api->getConfigValue(
                'lootlyordernotifier/advanced/custom_price_rule_id',
                $currentStoreId
            );
            if ($customRuleId >= 1) {
                /** @var \Magento\SalesRule\Api\Data\RuleInterface $dataModelOriginal */
                $dataModelOriginal = $this->ruleRepository->getById($customRuleId);
                $modelOriginal = $this->toModelConverter->toModel($dataModelOriginal);
                /*$modelOriginal = $this->ruleFactory->create();
                $modelOriginal->load($customRuleId);*/
                $data = $modelOriginal->getData();
                $couponCode = $dataRecived['title'];
                unset($data['rule_id']);
                unset($data['name']);
                unset($data['coupon_code']);
                unset($data['condition']);
                unset($data['action_condition']);
                $data['name'] = 'Lootly ' . $dataRecived['title'] . ' (copied rule '.$customRuleId.')';
                $data['coupon_code'] = $couponCode;
                $model->setData($data);
                $conditions = $dataModelOriginal->getCondition();
                $subConditions = $conditions->getConditions();
                if (isset($dataRecived['prerequisite_customer_ids'])
                    && count($dataRecived['prerequisite_customer_ids'])) {
                    $subConditions[] = $this->toDataModelConverter->arrayToConditionDataModel([
                        'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
                        'attribute' => 'customer_id',
                        'operator' => '()',
                        'value' => implode(',', $dataRecived['prerequisite_customer_ids'])
                    ]);
                }
                if (isset($dataRecived['prerequisite_customer_emails'])
                    && count($dataRecived['prerequisite_customer_emails'])) {
                    $emails = $dataRecived['prerequisite_customer_emails'];
                    $subConditions[] = $this->toDataModelConverter->arrayToConditionDataModel([
                        'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
                        'attribute' => 'email',
                        'operator' => '()',
                        'value' => implode(',', $emails)
                    ]);
                }
                $conditions->setConditions($subConditions);
                $newDataModel = $this->toDataModelConverter->toDataModel($model);
                $newDataModel->setCondition($conditions);
                $newDataModel->setActionCondition($dataModelOriginal->getActionCondition());
                $newDataModel = $this->ruleRepository->save($newDataModel);
                $model->load($newDataModel->getRuleId());
                $model->addData($data);
                $model->save();
            } else {
                $websiteIds[] = $this->storeManager->getStore()->getWebsiteId();

                $groups = [];
                $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
                foreach ($customerGroups as $group) {
                    $groups[] = $group->getId();
                }
                                //$groups[] = 0;
                $couponType = 2;
                //$couponCode = strtoupper(substr(md5('lootly' . microtime()), 0, 10));
                $couponCode = $dataRecived['title'];
                /** @var \Magento\Framework\Stdlib\DateTime $fromDate */

                $simpleFreeShipping = 0;
                if (isset($dataRecived['ends_at']) && $dataRecived['ends_at']) {
                    $toDate = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime');
                    $to_date = $toDate->gmDate('Y-m-d H:i:s', strtotime($dataRecived['ends_at']));
                }

                $simpleAction = 'by_fixed';
                if ($dataRecived['value_type'] == 'percentage') {
                    $simpleAction = 'by_percent';
                } elseif ($dataRecived['value_type'] == 'fixed_amount') {
                    $simpleAction = 'cart_fixed';
                }
                $discountAmount = abs($dataRecived['value']);
                $cartCondition = [
                    '1' => [
                        'type' => 'Magento\SalesRule\Model\Rule\Condition\Combine',
                        'aggregator' => 'all',
                        'value' => 1,
                        'new_child' => ''
                    ]
                ];
                $actions = [
                    '1' => [
                        'type' => 'Magento\SalesRule\Model\Rule\Condition\Product\Combine',
                        'aggregator' => 'all',
                        'value' => 1,
                        'new_child' => '',
                    ]
                ];
                $applyToShipping = 0;
                $additionalCartConditions = [];
                if (isset($dataRecived['prerequisite_subtotal_range'])
                    && isset($dataRecived['prerequisite_subtotal_range']['greater_than_or_equal_to'])) {
                    $additionalCartConditions[] = [
                        'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
                        'attribute' => 'base_subtotal',
                        'operator' => '>=',
                        'value' => $dataRecived['prerequisite_subtotal_range']['greater_than_or_equal_to']
                    ];
                }
                $prerequisite_customer_ids = [];
                if (isset($dataRecived['prerequisite_customer_ids'])
                    && count($dataRecived['prerequisite_customer_ids'])) {
                    $additionalCartConditions[] = [
                        'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
                        'attribute' => 'customer_id',
                        'operator' => '()',
                        'value' => implode(',', $dataRecived['prerequisite_customer_ids'])
                    ];
                    try {
                        $customer = $this->customerRepository->getById(
                            reset($dataRecived['prerequisite_customer_ids'])
                        );
                        if ($customer) {
                            $groups = [$customer->getGroupId()];
                        }
                    } catch (\Exception $e) {

                    }
                }
                if (isset($dataRecived['prerequisite_customer_emails'])
                    && count($dataRecived['prerequisite_customer_emails'])) {
                    $emails = $dataRecived['prerequisite_customer_emails'];
                    $additionalCartConditions[] = [
                        'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
                        'attribute' => 'email',
                        'operator' => '()',
                        'value' => implode(',', $emails)
                    ];
                }
                if (isset($dataRecived['target_type']) && $dataRecived['target_type'] == 'shipping_line') {
                    if ($dataRecived['value_type'] == 'percentage' && $dataRecived['value'] == '-100.0') {
                        $applyToShipping = 1;
                        $simpleAction = 'by_fixed';
                        $discountAmount = 0;
                        $simpleFreeShipping = 1;
                    } else {
                        return ['error' => 'shipping_line items can only have percentage -100%'];
                    }

                }
                $i = 1;
                foreach ($additionalCartConditions as $additionalCartCondition) {
                    $cartCondition['1--' . ($i++)] = $additionalCartCondition;
                }
                $discountQty = 0;
                /*if ($simpleAction == 'by_percent') {
                    $discountQty = 0;
                } else {
                    $discountQty = 1;
                }*/
                $addition = 1;
                if (isset($dataRecived['entitled_product_ids'])) {
                    //free product
                    $actions['1--' . ($addition++)] = [
                        'type' => 'Magento\SalesRule\Model\Rule\Condition\Product',
                        'attribute' => 'entity_id',
                        'operator' => '()',
                        'value' => implode(',', $dataRecived['entitled_product_ids'])
                    ];
                    $discountQty = 1;
                }
                if (isset($dataRecived['entitled_category_ids'])) {
                    //free product
                    $actions['1--' . ($addition++)] = [
                        'type' => 'Magento\SalesRule\Model\Rule\Condition\Product',
                        'attribute' => 'category_ids',
                        'operator' => '()',
                        'value' => implode(',', $dataRecived['entitled_category_ids'])
                    ];
                    /*if (!$discountAmount) {
                        $discountAmount = '100';
                    }*/
                    $discountQty = 1;
                }
                if (isset($dataRecived['excluded_product_ids'])) {
                    //free product
                    $actions['1--' . ($addition++)] = [
                        'type' => 'Magento\SalesRule\Model\Rule\Condition\Product',
                        'attribute' => 'entity_id',
                        'operator' => '!()',
                        'value' => implode(',', $dataRecived['excluded_product_ids'])
                    ];
                }
                if (isset($dataRecived['excluded_category_ids'])) {
                    //free product
                    $actions['1--' . ($addition++)] = [
                        'type' => 'Magento\SalesRule\Model\Rule\Condition\Product',
                        'attribute' => 'category_ids',
                        'operator' => '!()',
                        'value' => implode(',', $dataRecived['excluded_category_ids'])
                    ];
                }

                $uses_per_coupon = $dataRecived['usage_limit'];
                $uses_per_customer = $dataRecived['usage_limit'];
                if (isset($dataRecived['uses_per_coupon'])){
                    $uses_per_coupon = $dataRecived['uses_per_coupon'];
                }
                if (isset($dataRecived['uses_per_customer'])){
                    $uses_per_customer = $dataRecived['uses_per_customer'];
                }
                $data = [
                    'name' => 'Lootly ' . $dataRecived['title'],
                    'description' => 'Generated by Lootly',
                    'is_active' => 1,
                    'website_ids' => $websiteIds,
                    'customer_group_ids' => $groups,
                    'coupon_type' => $couponType,
                    'coupon_code' => $couponCode,
                    'uses_per_coupon' => $uses_per_coupon,
                    'uses_per_customer' => $uses_per_customer,
                    'from_date' => $from_date,
                    'to_date' => $to_date,
                    'sort_order' => 0,
                    'is_rss' => 0,
                    'rule' => [
                        'conditions' => $cartCondition,
                        'actions' => $actions
                    ],
                    'simple_action' => $simpleAction,
                    'discount_amount' => $discountAmount,
                    'discount_qty' => $discountQty,
                    'discount_step' => 0,
                    'apply_to_shipping' => $applyToShipping,
                    'simple_free_shipping' => $simpleFreeShipping,
                    'stop_rules_processing' => 0,
                    'store_labels' => []
                ];

                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new NoSuchEntityException(__('Wrong rule specified.'));
                    }
                }

                if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
                    && isset($data['discount_amount'])
                ) {
                    $data['discount_amount'] = min(100, $data['discount_amount']);
                }
                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                unset($data['rule']);
                $model->loadPost($data);
                $useAutoGeneration = 0;
                $model->setUseAutoGeneration($useAutoGeneration);

                $model->save();
            }
            $coupon = $model->getPrimaryCoupon();
            $coupon->setCode($couponCode);
            $coupon->unsetData('expiration_date');
            $coupon->afterLoad();
            $coupon->setHasDataChanges(true);
            $coupon->setExpirationDate($to_date);
            $coupon->save();
            $autoApplyCoupon = $this->api->getAutoApplyCoupon();
            $autoApplyFreeProduct = $this->api->getAutoApplyFreeProduct();
            try {
                if (isset($dataRecived['prerequisite_customer_ids'])
                    && count($dataRecived['prerequisite_customer_ids'])
                    && ($autoApplyCoupon == 1 || $autoApplyFreeProduct)
                ) {
                    $customerId = reset($dataRecived['prerequisite_customer_ids']);
                    $customerCart = $this->cartRepository->getActiveForCustomer($customerId);
                    if ($autoApplyFreeProduct && isset($dataRecived['entitled_product_ids']) && $dataRecived['entitled_product_ids']) {
                        $product = $this->productFactory->create();
                        $product = $product->load(reset($dataRecived['entitled_product_ids']));
                        $customerCart->addProduct($product);
                        if (!$autoApplyCoupon){
                            $this->quoteRepository->save($customerCart);
                        }
                    }
                    if ($autoApplyCoupon) {
                        $customerCart->setCouponCode($coupon->getCode())->collectTotals();
                        $this->quoteRepository->save($customerCart);
                    }
                }
            } catch (\Exception $e) {
                $e->getMessage();
            }

            $result = [
                'id' => $model->getRuleId()
            ];
            $result = array_merge($result, $dataRecived);
            $result = ['price_rule' => $result];
        }
        return $result;
    }

    /**
     * Get customer Request
     *
     * @return array|string[]
     */
    protected function _customerRequest()
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        if (!$this->_validateHash()) {
            return ['error' => 'Invalid Hmac'];
        }
        try {
            $postdata = $this->_getRequestData();
            $customer = $this->customerRepository->getById($postdata['customer_id']);
            $addresses = $customer->setAddresses();
            $phone = '';
            $country = '';
            $zipcode = '';
            /** @var \Magento\Customer\Api\Data\AddressInterface $address */
            foreach ($addresses as $address) {
                if (!$phone) {
                    $phone = $address->getTelephone();
                }
                if (!$country) {
                    $country = $address->getCountryId();
                }
                if (!$zipcode) {
                    $zipcode = $address->getPostcode();
                }
            }
            $customerData = [
                'first_name' => $customer->getFirstname(),
                'last_name' => $customer->getLastname(),
                'email' => $customer->getEmail(),
                'phone' => $phone,
                'birthday' => $customer->getDob(),
                'country' => $country,
                'zipcode' => $zipcode
            ];
            return $customerData;
        } catch (\Exception $e) {
            return ['error' => 'not_found', 'details' => $e->getMessage()];
        }
    }
}
