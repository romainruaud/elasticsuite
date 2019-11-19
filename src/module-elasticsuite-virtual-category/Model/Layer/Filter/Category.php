<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualCategory
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteVirtualCategory\Model\Layer\Filter;

use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * Product category filter implementation using virtual categories.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualCategory
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class Category extends \Smile\ElasticsuiteCatalog\Model\Layer\Filter\Category
{
    /**
     * @var \Smile\ElasticsuiteVirtualCategory\Model\Category\Filter\Provider
     */
    private $filterProvider;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection|\Magento\Catalog\Model\Category[]
     */
    private $childrenCategories;

    /**
     * @var \Smile\ElasticsuiteCore\Api\Search\ContextInterface
     */
    private $searchContext;

    /**
     * @var boolean
     */
    private $useUrlRewrites;

    /**
     * Constructor.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory                   $filterItemFactory   Filter item factory.
     * @param \Magento\Store\Model\StoreManagerInterface                        $storeManager        Store manager.
     * @param \Magento\Catalog\Model\Layer                                      $layer               Search layer.
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder              $itemDataBuilder     Item data builder.
     * @param \Magento\Framework\Escaper                                        $escaper             HTML escaper.
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory  $dataProviderFactory Data provider.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                $scopeConfig         Scope config.
     * @param \Smile\ElasticsuiteCore\Api\Search\ContextInterface               $context             Search context.
     * @param \Smile\ElasticsuiteVirtualCategory\Model\Category\Filter\Provider $filterProvider      Category Filter provider.
     * @param boolean                                                           $useUrlRewrites      Uses URLs rewrite for rendering.
     * @param array                                                             $data                Custom data.
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $dataProviderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Smile\ElasticsuiteCore\Api\Search\ContextInterface $context,
        \Smile\ElasticsuiteVirtualCategory\Model\Category\Filter\Provider $filterProvider,
        $useUrlRewrites = false,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $escaper,
            $dataProviderFactory,
            $scopeConfig,
            $context,
            $useUrlRewrites,
            $data
        );

        $this->searchContext  = $context;
        $this->filterProvider = $filterProvider;
        $this->useUrlRewrites = $useUrlRewrites;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $categoryId = $request->getParam($this->_requestVar) ? : $request->getParam('id');

        if (!empty($categoryId)) {
            $this->getDataProvider()->setCategoryId($categoryId);

            $category = $this->getDataProvider()->getCategory();

            // Default behavior, apply current category filter in any cases.
            $this->applyCategoryFilterToCollection($category);

            // Default behavior.
            $this->searchContext->setCurrentCategory($category);

            // If both params are present, we might be drilling down under a category subtree.
            if ($request->getParam($this->_requestVar) && $request->getParam('id')) {
                // Hacky : use dataProvider as a proxy to retrieve category model to avoid having to inject repository.
                $baseCategory = $this->getDataProvider()->setCategoryId($request->getParam('id'))->getCategory();
                // End of hacky : restore previous objects.
                $category = $this->getDataProvider()->setCategoryId($categoryId)->getCategory();

                // If base category is virtual, we are drilling down.
                if (true === ((bool) $baseCategory->getIsVirtualCategory())) {
                    // We have to also add the base category root query to the query.
                    $queryFilter = $this->filterProvider->getQueryFilter($baseCategory);
                    $this->getLayer()->getProductCollection()->addQueryFilter($queryFilter);
                    // No URL Rewrites when drilling down.
                    $this->setUseUrlRewrites(false);
                }
            }

            if ($request->getParam('id') != $category->getId() && $this->getDataProvider()->isValid()) {
                $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $categoryId));
            }
        }

        return $this;
    }

    /**
     * Indicates if the filter uses url rewrites or not.
     *
     * @return bool
     */
    protected function useUrlRewrites()
    {
        $category = $this->getDataProvider()->getCategory();

        // No url rewrites if viewing a virtual category having a root category.
        if ($category && $category->getIsVirtualCategory() && !empty($category->getVirtualCategoryRoot())) {
            return false;
        }

        return $this->useUrlRewrites;
    }

    /**
     * {@inheritDoc}
     */
    protected function getFilterField()
    {
        return 'categories';
    }

    /**
     * {@inheritDoc}
     */
    protected function applyCategoryFilterToCollection(\Magento\Catalog\Api\Data\CategoryInterface $category)
    {
        $query = $this->getFilterQuery();

        if ($query !== null) {
            $this->getLayer()->getProductCollection()->addQueryFilter($query);
        }

        return $this;
    }

    /**
     * Retrieve currently selected category children categories.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|\Magento\Catalog\Model\Category[]
     */
    protected function getChildrenCategories()
    {
        if ($this->childrenCategories === null) {
            $currentCategory = $this->getDataProvider()->getCategory();
            $this->childrenCategories = $currentCategory->getChildrenCategories();

            // Use the root category to retrieve children if needed.
            if ($this->useVirtualRootCategorySubtree($currentCategory) && !$this->searchContext->getCurrentSearchQuery()) {
                $this->childrenCategories = $this->getVirtualRootCategory($currentCategory)->getChildrenCategories();
                $this->childrenCategories->clear()->addFieldToFilter('entity_id', ['neq' => $currentCategory->getId()]);
            }
        }

        return $this->childrenCategories;
    }

    /**
     * Current category filter query.
     *
     * @return \Smile\ElasticsuiteCore\Search\Request\QueryInterface
     */
    private function getFilterQuery()
    {
        return $this->filterProvider->getQueryFilter($this->getDataProvider()->getCategory());
    }

    /**
     * Check if a category is configured to use its "virtual root category" to display facets
     *
     * @param CategoryInterface $category The category
     *
     * @return bool
     */
    private function useVirtualRootCategorySubtree($category)
    {
        $rootCategory = $this->getVirtualRootCategory($category);

        return ($rootCategory && $rootCategory->getId());
    }

    /**
     * Retrieve the Virtual Root Category of a category.
     *
     * @param CategoryInterface $category The category
     *
     * @return CategoryInterface
     */
    private function getVirtualRootCategory($category)
    {
        $virtualRule  = $category->getVirtualRule();
        $rootCategory = $virtualRule->getVirtualRootCategory($category);

        return $rootCategory;
    }

    /**
     * Set to use URL rewrites or not.
     *
     * @param bool $value The value
     *
     * @return $this
     */
    private function setUseUrlRewrites(bool $value)
    {
        $this->useUrlRewrites = $value;

        return $this;
    }
}
