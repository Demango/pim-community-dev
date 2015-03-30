<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\AbstractAttributeFilter;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Reference data filter
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var AttributeValidatorHelper */
    protected $attrValidatorHelper;

    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /** @var array */
    protected $supportedAttributes;

    /** @var OptionsResolverInterface */
    protected $resolver;

    /**
     * Instanciate the base filter
     *
     * @param AttributeValidatorHelper       $attrValidatorHelper
     * @param ConfigurationRegistryInterface $registry
     * @param array                          $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        ConfigurationRegistryInterface $registry,
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->registry = $registry;
        $this->supportedOperators  = $supportedOperators;

        $this->resolver = new OptionsResolver();
        $this->resolver->setRequired(['field']);
        $this->resolver->setOptional(['locale', 'scope']);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $scope = null,
        $options = []
    ) {
        try {
            $options = $this->resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidArgumentException::expectedFromPreviousException(
                $e,
                $attribute->getCode(),
                'filter',
                'options'
            );
        }

        $this->checkLocaleAndScope($attribute, $locale, $scope, 'reference_data');

        if (Operators::IS_EMPTY !== $operator) {
            $this->checkValue($options['field'], $value);
            $this->addNonEmptyFilter($attribute, $operator, $value, $locale, $scope);
        } else {
            $this->addEmptyFilter($attribute, $locale, $scope);
        }

        return $this;
    }

    /**
     * Add empty filter to the qb
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     */
    protected function addEmptyFilter(
        AttributeInterface $attribute,
        $locale = null,
        $scope = null
    ) {
        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode(), true);

        // inner join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

        $this->qb->leftJoin(
            $this->qb->getRootAlias() . '.values',
            $joinAlias,
            'WITH',
            $condition
        );

        $referenceDataName = $attribute->getReferenceDataName();
        $joinAliasOpt = $this->getUniqueAlias('reference_data' . $referenceDataName);
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');
        $condition    = $this->prepareCriteriaCondition($backendField, Operators::IS_EMPTY, null);
        $this->qb->leftJoin($joinAlias . '.' . $referenceDataName, $joinAliasOpt);
        $this->qb->andWhere($condition);
    }

    /**
     * Add non empty filter to the query
     *
     * @param AttributeInterface $attribute
     * @param string             $operator
     * @param string             $value
     * @param string             $locale
     * @param string             $scope
     */
    protected function addNonEmptyFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $scope = null
    ) {
        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode(), true);

        // inner join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

        $this->qb->innerJoin(
            $this->qb->getRootAlias() . '.values',
            $joinAlias,
            'WITH',
            $condition
        );

        $referenceDataName = $attribute->getReferenceDataName();
        $joinAliasOpt = $this->getUniqueAlias('reference_data' . $referenceDataName);
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');
        $condition    = $this->prepareCriteriaCondition($backendField, $operator, $value);
        $this->qb->innerJoin($joinAlias . '.' . $referenceDataName, $joinAliasOpt, 'WITH', $condition);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        $referenceDataName = $attribute->getReferenceDataName();

        return null !== $referenceDataName && null !== $this->registry->get($referenceDataName) ? true : false;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, 'reference_data');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, 'reference_data');
        }
    }
}
