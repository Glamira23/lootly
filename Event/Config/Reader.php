<?php

namespace Lootly\Lootly\Event\Config;

class Reader extends \Magento\Framework\Event\Config\Reader
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = ['/config/event' => 'name', '/config/event/observer' => 'name'];
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var array
     */
    protected $stopInfinityLoop = [];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Event\Config\Converter $converter
     * @param \Magento\Framework\Event\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface    $fileResolver,
        \Magento\Framework\Event\Config\Converter          $converter,
        \Magento\Framework\Event\Config\SchemaLocator      $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $fileName = 'events.xml',
        $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
        $defaultScope = 'global'
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }

    /**
     * Read
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $result = parent::read($scope);
        if (!isset($this->stopInfinityLoop[$scope])) {
            $this->stopInfinityLoop[$scope] = true;
            $orderStatus = $this->scopeConfig->getValue('lootlyordernotifier/general/order_status');
            if ($orderStatus == 'custom_event') {
                $customEventName = $this->scopeConfig->getValue('lootlyordernotifier/general/event_name');
                if ($customEventName) {
                    $result[$customEventName] = [
                        'lootlylootly_' . $customEventName => [
                            'instance' => \Lootly\Lootly\Observer\CustomEvent::class,
                            'shared' => false,
                            'name' => 'lootlylootly_' . $customEventName
                        ]
                    ];
                }
            }
        }
        return $result;
    }
}
