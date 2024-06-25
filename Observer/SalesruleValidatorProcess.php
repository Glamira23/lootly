<?php

/**
 * Lootly_Lootly extension
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Observer;

class SalesruleValidatorProcess implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Lootly\Lootly\Helper\Data $orderNotifierHelper
     * @param \Lootly\Lootly\Helper\Api $orderNotifierApiHelper
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Lootly\Lootly\Helper\Data $orderNotifierHelper,
        \Lootly\Lootly\Helper\Api $orderNotifierApiHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->orderNotifierHelper = $orderNotifierHelper;
        $this->orderNotifierApiHelper = $orderNotifierApiHelper;
        $this->dateTime = $dateTime;
    }

    /**
     * Validate sales rule
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $rule = $observer->getData('rule');
        $quote = $observer->getData('quote');
        $result = $observer->getData('result');
        $coupon = $rule->getPrimaryCoupon();
        $expiration_date = $coupon->getData('expiration_date');
        if (strpos($rule->getName(), 'Lootly') !== false
            && $rule->getData('to_date') != $expiration_date
            && $expiration_date != '0000-00-00 00:00:00') {
            /** @var \Magento\Framework\Stdlib\DateTime $fromDate */
            $now = strtotime('now');
            $expiration_date = strtotime($expiration_date);
            if ($expiration_date < $now) {
                $result->setAmount('0');
                $result->setBaseAmount('0');
                $quote->setData('coupon_code', '');
            }
        }
    }
}
