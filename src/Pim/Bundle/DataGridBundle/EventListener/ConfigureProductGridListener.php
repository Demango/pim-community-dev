<?php

namespace Pim\Bundle\DataGridBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfigurationRegistry;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ConfiguratorInterface;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ColumnsConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\SortersConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Product\FiltersConfigurator;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Doctrine\ORM\EntityRepository;

/**
 * Grid listener to configure columns, filters and sorters based on product attributes and business rules
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigureProductGridListener
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var ConfigurationRegistry
     */
    protected $confRegistry;

    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /** @var EntityRepository */
    protected $gridViewRepository;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param ProductManager           $productManager     product manager
     * @param ConfigurationRegistry    $confRegistry       attribute type configuration registry
     * @param RequestParameters        $requestParams      request parameters
     * @param SecurityContextInterface $securityContext    the security context
     * @param EntityRepository         $gridViewRepository DatagridView repository
     */
    public function __construct(
        ProductManager $productManager,
        ConfigurationRegistry $confRegistry,
        RequestParameters $requestParams,
        SecurityContextInterface $securityContext,
        EntityRepository $gridViewRepository
    ) {
        $this->productManager     = $productManager;
        $this->confRegistry       = $confRegistry;
        $this->requestParams      = $requestParams;
        $this->securityContext    = $securityContext;
        $this->gridViewRepository = $gridViewRepository;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Configure product columns, filters, sorters dynamically
     *
     * @param BuildBefore $event
     *
     * @throws \LogicException
     */
    public function buildBefore(BuildBefore $event)
    {
        $datagridConfig = $event->getConfig();

        $this->getContextConfigurator($datagridConfig)->configure();
        $this->getColumnsConfigurator($datagridConfig)->configure();
        $this->getSortersConfigurator($datagridConfig)->configure();
        $this->getFiltersConfigurator($datagridConfig)->configure();
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return ConfiguratorInterface
     */
    protected function getContextConfigurator(DatagridConfiguration $datagridConfig)
    {
        return new ContextConfigurator(
            $datagridConfig,
            $this->productManager,
            $this->requestParams,
            $this->request,
            $this->securityContext,
            $this->gridViewRepository
        );
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return ConfiguratorInterface
     */
    protected function getColumnsConfigurator(DatagridConfiguration $datagridConfig)
    {
        return new ColumnsConfigurator($datagridConfig, $this->confRegistry);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return ConfiguratorInterface
     */
    protected function getSortersConfigurator(DatagridConfiguration $datagridConfig)
    {
        return new SortersConfigurator($datagridConfig, $this->confRegistry);
    }

    /**
     * @param DatagridConfiguration $datagridConfig
     *
     * @return ConfiguratorInterface
     */
    protected function getFiltersConfigurator(DatagridConfiguration $datagridConfig)
    {
        return new FiltersConfigurator($datagridConfig, $this->confRegistry);
    }
}
