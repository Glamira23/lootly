<?php

namespace Lootly\Lootly\Block\Adminhtml;

/**
 * Synchronize button renderer
 */
class Verify extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var string
     */
    protected $_template = 'Lootly_Lootly::system/config/verify.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

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
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl("lootly_verify/system_config/verify");
    }

    /**
     * Generate synchronize button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'verify_lootly_settings',
                'label' => __('Verify Settings and Integrate'),
            ]
        );

        return $button->toHtml();
    }
}
