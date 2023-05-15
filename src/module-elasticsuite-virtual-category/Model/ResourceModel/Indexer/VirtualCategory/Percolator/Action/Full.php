<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualCategory
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2023 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteVirtualCategory\Model\ResourceModel\Indexer\VirtualCategory\Percolator\Action;

use Smile\ElasticsuiteCore\Model\ResourceModel\Indexer\AbstractIndexer;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Virtual Category indexer (percolator)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualCategory
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Full extends AbstractIndexer
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Constructor.
     *
     * @param ResourceConnection        $resource                  Database adpater.
     * @param StoreManagerInterface     $storeManager              Store manager.
     * @param CategoryCollectionFactory $categoryCollectionFactory Category collection factory
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        parent::__construct($resource, $storeManager);
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Load a bulk of categories data.
     *
     * @param int        $storeId     Store id.
     * @param array|null $categoryIds Target rule ids filter
     * @param integer    $fromId      Load from rule id greater than.
     * @param integer    $limit       Number of rules to load.
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVirtualCategories($storeId, $categoryIds = null, $fromId = 0, $limit = 100)
    {
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection
         */
        $collection = $this->categoryCollectionFactory->create();

        if ($categoryIds !== null) {
            $collection->addIdFilter($categoryIds);
        }

        $collection->addFieldToFilter('entity_id', ['gt' => $fromId])
                   ->addAttributeToSelect(['virtual_category_root', 'is_virtual_category', 'virtual_rule'])
                   ->setStoreId($storeId)
                   ->addIsActiveFilter()
                   ->addIdFilter($categoryIds)
                   ->addFieldToFilter('is_virtual_category', '1')
                   ->setPageSize($limit);

        return $this->connection->fetchAll($collection->getSelect());
    }
}
