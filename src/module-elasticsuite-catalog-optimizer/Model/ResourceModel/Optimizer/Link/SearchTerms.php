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
namespace Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Link;

use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;

/**
 * Resource Model to handle correlation between optimizer and search terms.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class SearchTerms extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Save link between optimizer and query ids.
     *
     * @param OptimizerInterface $optimizer The optimizer
     * @param integer[]          $queryIds  The query ids
     */
    public function saveOptimizerQueriesLink(OptimizerInterface $optimizer, $queryIds)
    {
        $rows = [];

        foreach ($queryIds as $queryId) {
            $rows[] = [
                OptimizerInterface::OPTIMIZER_ID  => (int) $optimizer->getId(),
                $this->getIdFieldName()           => (int) $queryId,
            ];
        }

        if ($optimizer->getId()) {
            $this->deleteByOptimizerId($optimizer->getId());
        }

        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $rows);
    }

    /**
     * Delete all links by optimizer Id
     *
     * @param int $optimizerId The optimizer Id
     *
     * @return void
     */
    public function deleteByOptimizerId($optimizerId)
    {
        $deleteCondition = $this->getConnection()->quoteInto(OptimizerInterface::OPTIMIZER_ID . " = ?", (int) $optimizerId);
        $this->getConnection()->delete($this->getMainTable(), $deleteCondition);
    }

    /**
     * Retrieve all search queries associated to a given optimizer Id.
     *
     * @param int $optimizerId The optimizer Id
     *
     * @return array
     */
    public function getQueryIdsByOptimizerId($optimizerId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), $this->getIdFieldName())
            ->where($this->getConnection()->quoteInto(OptimizerInterface::OPTIMIZER_ID . " = ?", (int) $optimizerId));

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Retrieve applicable optimizer Ids for a given query Id.
     *
     * @param int $queryId The query Id
     *
     * @return array
     */
    public function getApplicableOptimizerIdsByQueryId($queryId)
    {
        $select = $this->getConnection()
            ->select()
            ->from(['main_table' => $this->getMainTable()], [])
            ->joinInner(
                ['osc' => $this->getTable(OptimizerInterface::TABLE_NAME_SEARCH_CONTAINER)],
                "osc.optimizer_id = main_table.optimizer_id OR osc.apply_to = 0",
                [OptimizerInterface::OPTIMIZER_ID]
            )
            ->where($this->getConnection()->quoteInto("main_table.{$this->_idFieldName} = ?", (int) $queryId))
            ->group(OptimizerInterface::OPTIMIZER_ID);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Resource initialization
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) Method is inherited.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(OptimizerInterface::TABLE_NAME_SEARCH_TERMS, 'query_id');
    }
}
