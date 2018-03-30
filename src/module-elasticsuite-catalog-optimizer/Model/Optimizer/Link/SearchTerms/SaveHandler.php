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
namespace Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Link\SearchTerms;

/**
 * Save handler to process link between optimizers and search terms
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class SaveHandler implements \Magento\Framework\EntityManager\Operation\ExtensionInterface
{
    /**
     * @var \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\SearchTerms
     */
    private $resource;

    /**
     * SearchTermsSaveHandler constructor.
     *
     * @param \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\SearchTerms $resource Resource
     */
    public function __construct(
        \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\SearchTerms $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        $searchContainerData = $entity->getData('quick_search_container');
        $applyTo = is_array($searchContainerData) ? ((bool) $searchContainerData['apply_to'] ?? false) : false;

        if ($applyTo === false) {
            $this->resource->deleteByOptimizerId($entity->getId());
        } elseif (isset($searchContainerData['query_ids']) && !empty($searchContainerData['query_ids'])) {
            $ids = $queryIds = $searchContainerData['query_ids'];

            if (is_array(current($ids))) {
                $queryIds = [];
                foreach ($ids as $queryId) {
                    if (isset($queryId['id'])) {
                        $queryIds[] = (int) $queryId['id'];
                    }
                }
            }

            $this->resource->saveOptimizerQueriesLink($entity, $queryIds);
        }

        return $entity;
    }
}
