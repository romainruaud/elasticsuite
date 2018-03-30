<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalogOptimizer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link;

use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;

/**
 * Resource Model to handle correlation between optimizer and categories.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Categories extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * FilterableAttributeList constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context        Context
     * @param \Magento\Framework\EntityManager\MetadataPool     $metadataPool   Metadata Pool
     * @param null                                              $connectionName Connection Name
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    /**
     * Save link between optimizer and category ids.
     *
     * @param \Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface $optimizer   The optimizer
     * @param integer[]                                                       $categoryIds The category ids
     */
    public function saveOptimizerCategoriesLink(OptimizerInterface $optimizer, $categoryIds)
    {
        $rows = [];

        foreach ($categoryIds as $categoryId) {
            $rows[] = [
                OptimizerInterface::OPTIMIZER_ID  => (int) $optimizer->getId(),
                $this->getIdFieldName()           => (int) $categoryId,
            ];
        }

        if ($optimizer->getId()) {
            $this->deleteByOptimizerId($optimizer->getId());
        }

        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $rows);
    }

    /**
     * Delete all links by optimizer Id
     *
     * @param int $optimizerId The optimizer Id
     *
     * @return void
     */
    public function deleteByOptimizerId($optimizerId)
    {
        $deleteCondition = $this->getConnection()->quoteInto(OptimizerInterface::OPTIMIZER_ID . " = ?", (int) $optimizerId);
        $this->getConnection()->delete($this->getMainTable(), $deleteCondition);
    }

    /**
     * Retrieve all categories associated to a given optimizer Id.
     *
     * @param int $optimizerId The optimizer Id
     *
     * @return array
     */
    public function getCategoryIdsByOptimizerId($optimizerId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), $this->getIdFieldName())
            ->where($this->getConnection()->quoteInto(OptimizerInterface::OPTIMIZER_ID . " = ?", (int) $optimizerId));

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Resource initialization
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) Method is inherited.
     *
     * @return void
     */
    protected function _construct()
    {
        $idField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\CategoryInterface::class)->getIdentifierField();
        $this->_init(OptimizerInterface::TABLE_NAME_CATEGORIES, $idField);
    }
}
