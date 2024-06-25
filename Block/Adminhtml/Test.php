<?php

namespace Lootly\Lootly\Block\Adminhtml;

/**
 * Lootly_Lootly Adminhtml Block for Connection Test
 *
 */
class Test extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var \Lootly\Lootly\Helper\Api
     */
    protected $orderNotifierApiHelper;
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var string
     */
    protected $_template = 'Lootly_Lootly::system/config/test.phtml';

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Lootly\Lootly\Helper\Api $orderNotifierApiHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        array $data = []
    ) {
        $this->orderNotifierApiHelper = $orderNotifierApiHelper;
        $this->_resourceConfig = $resourceConfig;
        $this->_assetRepo = $context->getAssetRepository();
        parent::__construct($context, $data);
    }

    /*public function __construct(
        \Lootly\Lootly\Helper\Api $orderNotifierApiHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\View\Asset\Repository $_assetRepo
    ) {
        $this->orderNotifierApiHelper = $orderNotifierApiHelper;
        $this->_resourceConfig = $resourceConfig;
        $this->_assetRepo = $_assetRepo;
    }*/

    /**
     * Remove scope label
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Prepares Top HTML for Lootly Settings page
     *
     * @access protected
     * @author Lootly Inc
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getSignupUrl()
    {
        $signupURL = \Lootly\Lootly\Helper\Data::SIGNUP_URL;

        return $signupURL;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        $loginURL = \Lootly\Lootly\Helper\Data::LOGIN_URL;

        return $loginURL;
    }

    /**
     * @return bool
     */
    public function checkVerify()
    {
        $_helper = $this->orderNotifierApiHelper;

        if ($merchantId = $_helper->verify()) {
            $this->_resourceConfig->saveConfig(
                'lootlyordernotifier/general/merchant_id',
                $merchantId,
                'default',
                0
            );
            return true;
        } else {
            return false;
        }
    }
}
