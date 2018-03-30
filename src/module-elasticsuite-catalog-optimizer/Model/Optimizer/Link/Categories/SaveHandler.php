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
namespace Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Link\Categories;

/**
 * Save handler to process link between optimizers and categories
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class SaveHandler implements \Magento\Framework\EntityManager\Operation\ExtensionInterface
{
    /**
     * @var \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\Categories
     */
    private $resource;

    /**
     * CategoriesSaveHandler constructor.
     *
     * @param \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\Categories $resource Resource
     */
    public function __construct(
        \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\Categories $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        $searchContainerData = $entity->getData('catalog_view_container');
        $applyTo = is_array($searchContainerData) ? ((bool) $searchContainerData['apply_to'] ?? false) : false;

        if ($applyTo === false) {
            $this->resource->deleteByOptimizerId($entity->getId());
        } elseif (isset($searchContainerData['category_ids']) && !empty($searchContainerData['category_ids'])) {
            $this->resource->saveOptimizerCategoriesLink($entity, $searchContainerData['category_ids']);
        }

        return $entity;
    }
}
