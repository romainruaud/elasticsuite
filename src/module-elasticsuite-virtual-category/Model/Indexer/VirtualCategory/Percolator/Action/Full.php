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

namespace Smile\ElasticsuiteVirtualCategory\Model\Indexer\VirtualCategory\Percolator\Action;

use \Smile\ElasticsuiteVirtualCategory\Model\ResourceModel\Indexer\VirtualCategory\Percolator\Action\Full as ResourceModel;

/**
 * Virtual Category indexer (percolator)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualCategory
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Full
{
    /**
     * @var \Smile\ElasticsuiteVirtualCategory\Model\ResourceModel\Indexer\VirtualCategory\Percolator\Action\Full
     */
    protected $resourceModel;

    /**
     * Constructor.
     *
     * @param ResourceModel $resourceModel Indexer resource model.
     */
    public function __construct(ResourceModel $resourceModel)
    {
        $this->resourceModel  = $resourceModel;
    }

    /**
     * Get data for a list of categories in a store id.
     * If the list of category ids is null, all categories data will be loaded.
     *
     * @param integer    $storeId     Store id.
     * @param array|null $categoryIds List of category ids.
     *
     * @return \Traversable
     */
    public function rebuildStoreIndex($storeId, $categoryIds = null)
    {
        $lastCategoryId = 0;

        do {
            $categories = $this->getVirtualCategories($storeId, $categoryIds, $lastCategoryId);

            foreach ($categories as $categoryData) {
                $lastCategoryId = (int) $categoryData['rule_id'];
                yield $lastCategoryId => $categoryData;
            }
        } while (!empty($categories));
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
     */
    private function getVirtualCategories($storeId, $categoryIds = null, $fromId = 0, $limit = 100)
    {
        return $this->resourceModel->getVirtualCategories($storeId, $categoryIds, $fromId, $limit);
    }
}
