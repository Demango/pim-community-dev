<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface;

/**
 * Product in group sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InGroupSorter implements SorterInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $repository;

    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * @param ProductRepositoryInterface $repository
     * @param RequestParameters          $requestParams
     */
    public function __construct(ProductRepositoryInterface $repository, RequestParameters $requestParams)
    {
        $this->repository    = $repository;
        $this->requestParams = $requestParams;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        $qb = $datasource->getQueryBuilder();

        $groupId = $this->requestParams->get('currentGroup', null);
        if (!$groupId) {
            throw new \LogicException('The current product group must be configured');
        }

        $field = 'in_group_'.$groupId;
        $pqb = $this->repository->getProductQueryBuilder($qb);
        $pqb->addSorter($field, $direction);
    }
}
