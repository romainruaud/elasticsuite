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
namespace Smile\ElasticsuiteCatalogOptimizer\Ui\Component\Optimizer\Form\Modifier;

/**
 * Optimizer Ui Component Modifier.
 * Used to populate search queries dynamicRows.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class SearchContainers implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Search Terms constructor.
     *
     * @param \Magento\Framework\Registry $registry Registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $optimizer = $this->getCurrentOptimizer();

        if ($optimizer && isset($data[$optimizer->getId()])) {
            $data[$optimizer->getId()]['search_container'] = array_keys($optimizer->getSearchContainers());
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get current optimizer
     *
     * @return \Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface
     */
    private function getCurrentOptimizer()
    {
        $optimizer = $this->registry->registry('current_optimizer');

        return $optimizer;
    }
}
