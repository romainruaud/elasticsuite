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

namespace Smile\ElasticsuiteVirtualCategory\Model\Indexer\VirtualCategory;

use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteCore\Indexer\GenericIndexerHandler;
use Smile\ElasticsuiteVirtualCategory\Model\Indexer\VirtualCategory\Percolator\Action\Full;

/**
 * Virtual Category indexer (percolator)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualCategory
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Percolator implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Indexer ID
     *
     * @var string
     */
    const INDEXER_ID = 'elasticsuite_virtualcategory_percolators';

    /**
     * Elasticsearch index identifier
     */
    const INDEX_IDENTIFIER = 'virtual_category';

    /**
     * @var GenericIndexerHandler
     */
    private $indexerHandler;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var Full
     */
    private $fullAction;

    /**
     * Constructor
     *
     * @param Full                  $fullAction       The full index action
     * @param GenericIndexerHandler $indexerHandler   The index handler
     * @param StoreManagerInterface $storeManager     The Store Manager
     * @param DimensionFactory      $dimensionFactory The dimension factory
     */
    public function __construct(
        Full $fullAction,
        GenericIndexerHandler $indexerHandler,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory
    ) {
        $this->fullAction       = $fullAction;
        $this->indexerHandler   = $indexerHandler;
        $this->storeManager     = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($ids)
    {
        $storeIds = array_keys($this->storeManager->getStores());

        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $this->indexerHandler->deleteIndex([$dimension], new \ArrayObject($ids));
            $this->indexerHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId, $ids));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function executeFull()
    {
        $storeIds = array_keys($this->storeManager->getStores());

        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $this->indexerHandler->cleanIndex([$dimension]);
            $this->indexerHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function executeList(array $ruleIds)
    {
        $this->execute($ruleIds);
    }

    /**
     * {@inheritDoc}
     */
    public function executeRow($ruleId)
    {
        $this->execute([$ruleId]);
    }
}
