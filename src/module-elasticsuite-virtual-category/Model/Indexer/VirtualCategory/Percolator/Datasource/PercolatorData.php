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

namespace Smile\ElasticsuiteVirtualCategory\Model\Indexer\VirtualCategory\Percolator\Datasource;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Smile\ElasticsuiteCore\Api\Index\DatasourceInterface;
use \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Request\Query\Builder as QueryBuilder;

/**
 * Append Virtual Categories Rule data into a percolated index.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualCategory
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class PercolatorData implements DatasourceInterface
{
    /**
     * The percolator type for this entity
     */
    const PERCOLATOR_TYPE = 'virtualcategory';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Request\Query\Builder;
     */
    protected $queryBuilder;

    /**
     * PercolatorData constructor.
     * @param TargetRuleCollectionFactory $categoryCollectionFactory Target rule collection factory
     * @param QueryBuilder                $queryBuilder          ES query builder
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        QueryBuilder $queryBuilder
    ) {
        $this->ruleCollectionFactory = $categoryCollectionFactory;
        $this->queryBuilder          = $queryBuilder;
    }

    /**
     * Add category virtual rule percolator data to the index
     *
     * {@inheritDoc}
     */
    public function addData($storeId, array $indexData)
    {
        $collection->addFieldToFilter('entity_id', ['gt' => $fromId])
                   ->addAttributeToSelect(['virtual_category_root', 'is_virtual_category', 'virtual_rule'])
                   ->setStoreId($storeId)
                   ->addIsActiveFilter()
                   ->addIdFilter(array_keys($indexData))
                   ->addFieldToFilter('is_virtual_category', '1')
                   ->setPageSize($limit);

        foreach ($collection as $category) {
            $categoryId = $category->getId();
            $query      = $this->queryBuilder->buildQuery($category->getVirtualRule()->getCategorySearchQuery($category));

            $percolatorData = [
                'type'            => 'product',
                'percolator_type' => self::PERCOLATOR_TYPE,
                'query'           => $query,
            ];

            $categoryData = $indexData[$categoryId] + $percolatorData;
            if (isset($ruleData['is_active']))  {
                $categoryData['is_active'] = (bool) $ruleData['is_active'];
            }

            $indexData[$categoryId] = ['_category' => $categoryData];
        }

        return $indexData;
    }
}
