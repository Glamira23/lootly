<?php

/**
 * A Magento 2 module named Lootly/OrderNotifier
 * Copyright (C) 2016  2015
 * This file included in Lootly/OrderNotifier is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Lootly\Lootly\Controller\Adminhtml\System\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

class Verify extends \Magento\Backend\App\Action
{

    /**
     * @var \Lootly\Lootly\Helper\Api
     */
    protected $orderNotifierApiHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;
    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;
    /**
     * @var Pool
     */
    protected $cacheFrontendPool;
    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Lootly\Lootly\Helper\Api $orderNotifierApiHelper
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Framework\App\Request\Http $request
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Lootly\Lootly\Helper\Api $orderNotifierApiHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Request\Http $request,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->orderNotifierApiHelper = $orderNotifierApiHelper;
        $this->_resourceConfig = $resourceConfig;
        $this->request = $request;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $_helper = $this->orderNotifierApiHelper;
        $email = $this->request->getParam('email');
        $key = $this->request->getParam('apikey');
        $secret = $this->request->getParam('secret');
        $storeId = $this->request->getParam('storeid');
        $data = [
            'email' => $email,
            'key' => $key,
            'secret' => $secret
        ];
        if ($_helper->verify($data)) {
            /**
             * Settings are verified
             * Now saving Email and Api Key
             */
            $scope = 'default';

            if ($storeId) {
                $scope = 'store';
            } else {
                $scope = 'default';
                $storeId = 0;
            }
            $this->_resourceConfig->saveConfig(
                'lootlyordernotifier/general/email',
                $email,
                $scope,
                $storeId
            );
            $this->_resourceConfig->saveConfig(
                'lootlyordernotifier/general/api_key',
                $key,
                $scope,
                $storeId
            );
            $this->_resourceConfig->saveConfig(
                'lootlyordernotifier/general/api_secret',
                $secret,
                $scope,
                $storeId
            );
            $types = ['config'];

            foreach ($types as $type) {
                $this->cacheTypeList->cleanType($type);
            }
            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
            $_helper->installApp($key, $secret);

            $this->messageManager->addSuccess('The extension is connected to your Lootly account.');
        } else {
            $this->messageManager->addError('Error! Please check your account email and api key then retry.');
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
