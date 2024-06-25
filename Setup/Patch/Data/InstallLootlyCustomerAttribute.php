<?php

/**
 * Lootly_Lootly extension
 * @category       Lootly Extensions
 * @package        Lootly_Lootly
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Lootly\Lootly\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;

class InstallLootlyCustomerAttribute implements DataPatchInterface
{

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(EavSetupFactory $eavSetupFactory, ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function apply()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();
        if (!$eavSetup->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'lootly_registered')) {
            $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'lootly_registered',
                [
                    'type'         => 'varchar',
                    'label'        => 'Lootly Registered',
                    'input'        => 'hidden',
                    'required'     => false,
                    'visible'      => false,
                    'user_defined' => false,
                    'position'     => 999,
                    'system'       => 1,
                ]
            );
        }
    }
    /**
     * Get Dependencies
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases
     */
    public function getAliases()
    {
        return [];
    }
}
