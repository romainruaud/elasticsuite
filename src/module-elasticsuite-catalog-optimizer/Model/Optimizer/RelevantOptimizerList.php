<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalogOptimizer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer;

/**
 * Return relevant optimizers for a given search context.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class RelevantOptimizerList
{
    /**
     * @var \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\Categories
     */
    private $categoriesLinkResource;

    /**
     * @var \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\SearchTerms
     */
    private $searchTermsLinkResource;

    /**
     * @var array
     */
    private $relevantOptimizersByCategory = [];

    /**
     * @var array
     */
    private $relevantOptimizersBySearchTerm = [];

    /**
     * RelevantOptimizerList constructor.
     *
     * @param \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\Categories  $categoriesResource  Category Resource
     * @param \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\SearchTerms $searchTermsResource Search Term Resource
     */
    public function __construct(
        \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\Categories $categoriesResource,
        \Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link\SearchTerms $searchTermsResource
    ) {
        $this->categoriesLinkResource  = $categoriesResource;
        $this->searchTermsLinkResource = $searchTermsResource;
    }

    /**
     * Retrieve only relevant optimizers for a given search context.
     *
     * @param \Smile\ElasticsuiteCore\Api\Search\ContextInterface $context        Search context
     * @param array                                               $optimizersList Array of optimizers
     *
     * @return array
     */
    public function getRelevantOptimizers(
        \Smile\ElasticsuiteCore\Api\Search\ContextInterface $context,
        array $optimizersList
    ) {
        $optimizerIds = array_keys($optimizersList);

        if ($context->getCurrentCategory()) {
            $optimizerIds = $this->getByCategoryId($context);
        } elseif ($context->getCurrentSearchQuery() && $context->getCurrentSearchQuery()->getId()) {
            $optimizerIds = $this->getByQueryId($context);
        }

        return array_intersect_key($optimizersList, array_flip($optimizerIds));
    }

    /**
     * Get Relevant Optimizers by category Id.
     *
     * @param \Smile\ElasticsuiteCore\Api\Search\ContextInterface $context Search Context
     *
     * @return array
     */
    private function getByCategoryId(\Smile\ElasticsuiteCore\Api\Search\ContextInterface $context)
    {
        $storeId    = $context->getStoreId();
        $categoryId = (int) $context->getCurrentCategory()->getId();
        $cacheKey   = sprintf("%s_%s", $categoryId, $storeId);

        if (!isset($this->relevantOptimizersByCategory[$cacheKey])) {
            $this->relevantOptimizersByCategory[$cacheKey] = $this->categoriesLinkResource
                ->getApplicableOptimizerIdsByCategoryId($categoryId);
        }

        return $this->relevantOptimizersByCategory[$cacheKey];
    }

    /**
     * Get Relevant Optimizers by query Id.
     *
     * @param \Smile\ElasticsuiteCore\Api\Search\ContextInterface $context Search Context
     *
     * @return array
     */
    private function getByQueryId(\Smile\ElasticsuiteCore\Api\Search\ContextInterface $context)
    {
        $storeId  = $context->getStoreId();
        $queryId  = (int) $context->getCurrentSearchQuery()->getId();
        $cacheKey = sprintf("%s_%s", $queryId, $storeId);

        if (!isset($this->relevantOptimizersBySearchTerm[$cacheKey])) {
            $this->relevantOptimizersBySearchTerm[$cacheKey] = $this->searchTermsLinkResource
                ->getApplicableOptimizerIdsByQueryId($queryId);
        }

        return $this->relevantOptimizersBySearchTerm[$cacheKey];
    }
}
