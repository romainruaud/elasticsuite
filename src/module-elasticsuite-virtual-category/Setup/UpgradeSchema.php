<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualCategory
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteVirtualCategory\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * ElasticSuite virtual categories Schema update.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualCategory
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \Smile\ElasticsuiteVirtualCategory\Setup\VirtualCategorySetup
     */
    private $virtualCategorySetup;

    /**
     * InstallSchema constructor.
     *
     * @param VirtualCategorySetupFactory $virtualCategorySetupFactory VirtualCategorySetupFactory Setup Factory.
     */
    public function __construct(VirtualCategorySetupFactory $virtualCategorySetupFactory)
    {
        $this->virtualCategorySetup = $virtualCategorySetupFactory->create();
    }

    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface   $setup   Setup
     * @param ModuleContextInterface $context Context
     *
     * @return void
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->virtualCategorySetup->createVirtualCategoriesTable($setup);
        }

        $setup->endSetup();
    }
}
