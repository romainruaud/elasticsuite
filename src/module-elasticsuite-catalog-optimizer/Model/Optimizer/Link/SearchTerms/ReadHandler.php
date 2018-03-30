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
 * Read handler to load link between optimizers and search terms
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ReadHandler implements \Magento\Framework\EntityManager\Operation\ExtensionInterface
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
        $searchContainers = $entity->getResource()->getSearchContainersFromOptimizerId($entity->getId());
        $applyTo          = (bool) ($searchContainers['quick_search_container'] ?? false);

        if ($applyTo) {
            $containerData = ['apply_to' => (int) true];
            $queryIds      = $this->resource->getQueryIdsByOptimizerId($entity->getId());
            if (!empty($queryIds)) {
                $containerData['query_ids'] = $queryIds;
            }
            $entity->setData('quick_search_container', $containerData);
        }

        return $entity;
    }
}
