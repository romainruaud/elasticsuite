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
 * Read handler to load link between optimizers and categories
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ReadHandler implements \Magento\Framework\EntityManager\Operation\ExtensionInterface
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
        if ($entity->getId()) {
            $searchContainers = $entity->getResource()->getSearchContainersFromOptimizerId($entity->getId());
            $applyTo          = (bool) ($searchContainers['catalog_view_container'] ?? false);

            if ($applyTo) {
                $containerData = ['apply_to' => (int) true];
                $categoryIds   = $this->resource->getCategoryIdsByOptimizerId($entity->getId());
                if (!empty($categoryIds)) {
                    $containerData['category_ids'] = $categoryIds;
                }
                $entity->setData('catalog_view_container', $containerData);
            }
        }

        return $entity;
    }
}
