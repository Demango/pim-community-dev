<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\ORM;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Doctrine\ORM\EntityManager;
use Prophecy\Argument;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

class AttributeOptionReaderSpec extends ObjectBehavior {

    function let(
        EntityManager $entityManager
    ) {
        $this->beConstructedWith($entityManager, 'Pim\Bundle\CatalogBundle\Entity\AttributeOption');
    }

    function it_should_be_a_reader() {
        $this->shouldImplement('Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\Reader');
    }

    function it_should_create_a_sorted_query(
        EntityManager $entityManager,
        EntityRepository $entityRepository,
        QueryBuilder $qb,
        AbstractQuery $query
    ) {
        $entityManager->getRepository(Argument::any())->willReturn($entityRepository);
        $entityRepository->createQueryBuilder('ao')->willReturn($qb);
        $qb->orderBy('ao.attribute')->willReturn($qb);
        $qb->addOrderBy('ao.sortOrder')->willReturn($qb);
        $qb->getQuery()->willReturn($query);

        $this->getQuery()->shouldNotBeNull();
    }
} 
