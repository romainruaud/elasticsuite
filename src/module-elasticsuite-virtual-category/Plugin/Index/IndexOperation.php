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

namespace Smile\ElasticsuiteVirtualCategory\Plugin\Index;

/**
 * Plugin to append standard index mapping into virtualcategory index.
 * Virtual Category index must be aware of product-related indices mapping.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualCategory
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class IndexOperation
{
    /**
     * @var \Smile\ElasticsuiteCore\Api\Client\ClientInterface
     */
    private $client;

    /**
     * IndexOperation constructor.
     *
     * @param \Smile\ElasticsuiteCore\Api\Client\ClientInterface $client Elasticsearch client
     */
    public function __construct(\Smile\ElasticsuiteCore\Api\Client\ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param \Smile\ElasticsuiteCore\Api\Index\IndexOperationInterface $subject         Index Operation
     * @param \Smile\ElasticsuiteCore\Api\Index\IndexInterface          $result          Resulting index
     * @param string                                                    $indexIdentifier Index identifier
     * @param integer|string|\Magento\Store\Api\Data\StoreInterface     $store           Store (id, identifier or object).
     *
     * @return \Smile\ElasticsuiteCore\Api\Index\IndexInterface
     */
    public function afterCreateIndex(
        \Smile\ElasticsuiteCore\Api\Index\IndexOperationInterface $subject,
        \Smile\ElasticsuiteCore\Api\Index\IndexInterface $result,
        string $indexIdentifier,
                                                                  $store
    ) {
        // If we are rebuilding the virtualcategory index, we have to merge his mapping with the existing "product" mapping.
        if ($indexIdentifier === \Smile\ElasticsuiteVirtualCategory\Model\Indexer\VirtualCategory\Percolator::INDEX_IDENTIFIER) {
            if ($subject->indexExists('catalog_product',$store)) {
                $productIndex = $subject->getIndexByName('catalog_product', $store);
                $this->client->putMapping($result->getName(), $productIndex->getMapping()->asArray());
            }
        }

        return $result;
    }
}
