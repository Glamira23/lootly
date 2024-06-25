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

/**
 * Lootly_Lootly helper
 *
 */
class Api extends \Lootly\Lootly\Helper\Data
{
    /**
     *
     */
    public const LIVEURL = 'https://lootly.io';
    /**
     *
     */
    public const TESTURL = 'https://lootly.grebola.com';
    /**
     * Verify Settings API URL
     */
    public const VERIFY_URL = '/integrations/webhooks/magento/key-verify';

    /**
     * Add Review API URL
     */
    //CONST REVIEW_URL = 'https://Lootly.io/api/merchant/add_review';
    /**
     *
     */
    public const SHIPPED_URL = '/integrations/webhooks/magento/orders-fulfilled';
    /**
     *
     */
    public const PROCESSING_URL = '/integrations/webhooks/magento/orders-processing';
    /**
     *
     */
    public const COMPLETED_URL = '/integrations/webhooks/magento/orders-completed';

    /**
     *
     */
    public const APPINSTALL_URL = '/integrations/webhooks/magento/app-installed';
    /**
     *
     */
    public const ORDERPAID_URL = '/integrations/webhooks/magento/orders-paid';
    /**
     *
     */
    public const NEWCUSTOMER_URL = '/integrations/webhooks/magento/customers-create';
    /**
     *
     */
    public const REFUND_URL = '/integrations/webhooks/magento/refunds-create';
    /**
     *
     */
    public const CANCELED_URL = '/integrations/webhooks/magento/orders-cancelled';

    /**
     *
     */
    public const FRONTSCRIPT_URL = '/js/integrations/shopify/script.js?shop=';

    /**
     * Get the verify url of Lootly API
     *
     * @access public
     * @return string
     * @author Lootly Inc
     */
    public function getLiveTestUrl()
    {
        if ($this->scopeConfig->getValue('lootlyordernotifier/general/testmode')) {
            return self::TESTURL;
        } else {
            return self::LIVEURL;
        }
    }

    /**
     * Verify Url
     *
     * @return string
     */
    public function getVerifyURL()
    {
        return self::getLiveTestUrl() . self::VERIFY_URL;
    }

    /**
     * Instllation Url
     *
     * @return string
     */
    public function getAppInstallURL()
    {
        return self::getLiveTestUrl() . self::APPINSTALL_URL;
    }

    /**
     * Order paid url
     *
     * @return string
     */
    public function getOrderPaidURL()
    {
        return self::getLiveTestUrl() . self::ORDERPAID_URL;
    }

    /**
     * Order shippid url
     *
     * @return string
     */
    public function getOrderShippedURL()
    {
        return self::getLiveTestUrl() . self::SHIPPED_URL;
    }

    /**
     * Order processing Url
     *
     * @return string
     */
    public function getOrderProcessingURL()
    {
        return self::getLiveTestUrl() . self::PROCESSING_URL;
    }

    /**
     * Order completed url
     *
     * @return string
     */
    public function getOrderCompletedURL()
    {
        return self::getLiveTestUrl() . self::COMPLETED_URL;
    }

    /**
     * New customer url
     *
     * @return string
     */
    public function getNewCustomerURL()
    {
        return self::getLiveTestUrl() . self::NEWCUSTOMER_URL;
    }

    /**
     * Refund Url
     *
     * @return string
     */
    public function getRefundURL()
    {
        return self::getLiveTestUrl() . self::REFUND_URL;
    }

    /**
     * Canceled Url
     *
     * @return string
     */
    public function getCanceledURL()
    {
        return self::getLiveTestUrl() . self::CANCELED_URL;
    }

    /**
     * Script Url
     *
     * @return string
     */
    public function getScriptUrl()
    {
        return self::getLiveTestUrl() . self::FRONTSCRIPT_URL . $this->_urlBuilder->getBaseUrl();
    }

    /**
     * Verify Lootly API Connection
     *
     * @access public
     * @param array $dataVerify
     * @return bool
     * @author Lootly Inc
     */
    public function verify($dataVerify = [])
    {
        $storeId = $this->_getRequest()->getParam('store');
        $verifyUrl = $this->getVerifyURL();
        $key = $this->getApiKey($storeId);
        if (isset($dataVerify['key'])) {
            $key = $dataVerify['key'];
        }
        if (!$key) {
            return false;
        }
        $secret = $this->getApiSecret($storeId);
        if (isset($dataVerify['secret'])) {
            $secret = $dataVerify['secret'];
        }
        $data = [
            'key' => $key,
            't' => time()
        ];
        ksort($data);
        $data['hmac'] = base64_encode(hash_hmac('sha256', json_encode($data), $secret, true));

        $result = $this->call($data, $verifyUrl);
        $status = $result['status'];
        return ($status > 0) ? $status : false;
    }

    /**
     * Install App
     *
     * @param string $apiKey
     * @param string $apiSecret
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function installApp($apiKey, $apiSecret)
    {
        $storeIds = [];
        $stores = $this->storeManager->getStores();
        $installedUrl = $this->storeManager->getStore()->getBaseUrl();
        foreach ($stores as $store) {
            $key = $this->getApiKey($store->getId());
            if ($apiKey==$key || !$installedUrl) {
                $installedUrl = $this->storeManager->getStore($store->getId())->getBaseUrl();
                break;
            }
        }
        $installedUrl = str_replace('/index.php/', '/', $installedUrl);
        $requestParams = [
            "shop_url" => $installedUrl,
            "api_endpoint" => $this->_getUrl('lootly/create_coupon/index', ['_nosid' => true]),
            "key" => $apiKey,
        ];
        ksort($requestParams);
        $requestParams["hmac"] = base64_encode(hash_hmac('sha256', json_encode($requestParams), $apiSecret, true));
        $this->call($requestParams, $this->getAppInstallURL());
    }

    /**
     * Call Lootly API
     *
     * @access public
     * @param array $data
     * @param string $url
     * @return object
     * @author Lootly Inc
     */

    public function call($data, $url)
    {
        $dataString = json_encode($data);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString)
        ]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString); // Insert the data
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($curl);
        $responseInfo = curl_getinfo($curl);
        curl_close($curl);
        $this->_logger->debug($result);
        if (in_array($responseInfo['http_code'], ['200', '201'])) {
            $res = ['status' => 1];
        } else {
            $res = ['status' => 0];
        }

        return $res;
    }

    /**
     * Send to Lootly Api
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $targetStatus
     */
    public function sendToApi($order, $targetStatus = null)
    {
        $storeId = $order->getStoreId();
        $model = $this->orderNotifierEntryFactory->create();
        $entries = $model->getCollection()->addFieldToFilter('order_id', $order->getIncrementId());
        if ($entries->getSize() <= 0) {
            $orderId = $order->getIncrementId();
            $secretKey = $this->getApiSecret($storeId);

            /* Get Order Total, Shipping country, zipcode, Order Coupon code */
            $orderTotal = $order->getBaseGrandTotal();
            $_shippingAddress = $order->getShippingAddress();
            $shippingZipcode = $_shippingAddress->getPostcode();
            $shippingCountry = $_shippingAddress->getCountryId();
            $taxAmount = $order->getTaxAmount();
            $couponCode = $order->getCouponCode();
            $discount_codes = [];
            if ($couponCode) {
                $discount_codes[] = ['code' => $couponCode];
            }
            $customerId = $order->getCustomerId();
            if (!$customerId) {
                $websiteId = $order->getStore()->getWebsite()->getId();
                try {
                    $customer = $this->customerRepository->get($order->getCustomerEmail(), $websiteId);
                    $customerId = $customer->getId();
                } catch (\Exception $e) {
                    $customerId = null;
                }
            }
            $customerInfo = [
                'email' => $order->getCustomerEmail()
            ];
            $firstname = $order->getCustomerFirstname();
            if ($firstname) {
                $customerInfo['first_name'] = $firstname;
            } else {
                $customerInfo['first_name'] = $order->getBillingAddress()->getFirstname();
            }
            $last_name = $order->getCustomerLastname();
            if ($last_name) {
                $customerInfo['last_name'] = $last_name;
            } else {
                $customerInfo['last_name'] = $order->getBillingAddress()->getLastname();
            }
            if ($customerId) {
                $customerInfo['id'] = $customerId;
                $address = $order->getShippingAddress();
                if ($address && $address->getId()) {
                    $customerInfo['default_address'] = [
                        'zip' => $address->getPostcode(),
                        'country' => $address->getCountryId()
                    ];
                }
            }
            $customerDob = $order->getCustomerDob();
            if ($customerDob && $customerDob != '0000-00-00 00:00:00' && $customerDob != '0000-00-00') {
                $customerInfo['birthday'] = $customerDob;
            }
            $discount = abs($order->getDiscountAmount());
            $subtotal = $order->getSubtotal() - $discount;
            $productsData = [];
            $guest = $order->getCustomerIsGuest();
            foreach ($order->getAllVisibleItems() as $item) {
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $item->getProduct();
                if ($product === null) {
                    continue;
                }
                $categories = [];
                $cats = $product->getCategoryCollection();
                $cats->addAttributeToSelect('name');
                foreach ($cats as $category) {
                    $categories[] = [
                        'category_name' => $category->getName(),
                        'category_id' => $category->getId()
                    ];
                }
                $productData = [];
                $productData['product_id'] = $item->getProductId();
                $productData['product_name'] = $item->getName();
                $productData['product_price'] = $item->getBasePrice();
                $productData['quantity'] = $item->getQtyOrdered();
                $productData['categories'] = $categories;
                $productData['applied_rule_ids'] = $item->getAppliedRuleIds();
                $productsData[] = $productData;
            }

            $apiData = [
                "id" => $order->getIncrementId(),
                'total_price' => number_format($orderTotal, 2, '.', ''),
                'total_tax' => number_format($taxAmount, 2, '.', ''),
                'total_discounts' => number_format($discount, 2, '.', ''),
                'subtotal_price' => number_format($subtotal, 2, '.', ''),
                'taxes_included' => '0',
                'discount_codes' => $discount_codes,
                'customer' => $customerInfo,
                "key" => $this->getApiKey($storeId),
                "products" => $productsData,
                'ip_address' => $order->getRemoteIp(),
                'guest' => $guest
            ];
            /* Pass Coupon code to the API call If coupon code was used */
            if (!empty($couponCode)) {
                $apiData['coupon_code'] = $couponCode;
            }

            ksort($apiData);
            $apiData["hmac"] = base64_encode(hash_hmac('sha256', json_encode($apiData), $secretKey, true));

            $url = $this->getOrderProcessingURL();
            if ($targetStatus == 'shipped') {
                $url = $this->getOrderShippedURL();
            } elseif ($targetStatus == 'invoiced') {
                $url = $this->getOrderPaidURL();
            } elseif ($targetStatus == 'complete') {
                $url = $this->getOrderCompletedURL();
            }
            $result = $this->call($apiData, $url);

            if ($result['status'] == '1') {
                /*Mage::getSingleton('adminhtml/session')->addSuccess('Order info has been sent to Lootly.');*/
                $data = [
                    'order_id' => $order->getIncrementId()
                ];
                $model->setData($data);
                $model->save();
            } /*else {
                Mage::getSingleton('adminhtml/session')->addError('Error! Order info was not sent to Lootly.');
            }*/
        }
    }

    /**
     * Process canceled
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function processCanceled($order)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $storeId = $order->getStoreId();
        $apiData = [
            'id' => $order->getIncrementId(),
            "key" => $this->getApiKey($storeId),
        ];
        $secretKey = $this->getApiSecret($storeId);

        ksort($apiData);
        $apiData["hmac"] = base64_encode(hash_hmac('sha256', json_encode($apiData), $secretKey, true));

        $url = $this->getCanceledURL();
        $result = $this->call($apiData, $url);
    }
}
