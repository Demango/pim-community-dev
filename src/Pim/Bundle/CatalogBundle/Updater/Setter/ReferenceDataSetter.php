<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\MethodNameGuesser;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataSetter extends AbstractAttributeSetter
{
    /** @var DenormalizerInterface */
    protected $referenceDataDenormalizer;

    /**
     * @param ProductBuilderInterface  $productBuilder
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param DenormalizerInterface    $referenceDataDenormalizer
     * @param array                    $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        DenormalizerInterface $referenceDataDenormalizer,
        array $supportedTypes
    ) {
        parent::__construct($productBuilder, $attrValidatorHelper);

        $this->referenceDataDenormalizer = $referenceDataDenormalizer;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $this->checkLocaleAndScope($attribute, $options['locale'], $options['scope'], 'reference data');
        $this->checkData($attribute, $data);

        if (null !== $data) {
            $data = $this->referenceDataDenormalizer->denormalize($data, '', null, ['attribute' => $attribute]);

            if (null === $data) {
                throw new \LogicException('The refence data does not exist');
            }
        }

        $this->setReferenceData($attribute, $product, $data, $options['locale'], $options['scope']);
    }

    /**
     * Check if data is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $attribute->getCode(),
                'setter',
                'reference data',
                gettype($data)
            );
        }
    }

    /**
     * Set reference data into the product value
     *
     * @param AttributeInterface          $attribute
     * @param ProductInterface            $product
     * @param ReferenceDataInterface|null $referenceData
     * @param string|null                 $locale
     * @param string|null                 $scope
     *
     * @throws \LogicException
     */
    protected function setReferenceData(
        AttributeInterface $attribute,
        ProductInterface $product,
        $referenceData = null,
        $locale = null,
        $scope = null
    ) {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);

        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        $setMethod = MethodNameGuesser::guess('set', $attribute->getReferenceDataName(), true);

        if (false === method_exists($value, $setMethod)) {
            throw new \LogicException(
                sprintf('ProductValue method "%s" is not implemented', $setMethod)
            );
        }

        $value->$setMethod($referenceData);
    }
}