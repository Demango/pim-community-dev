<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Updater\Util\AttributeUtility;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Sets a media value in many products
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaValueSetter extends AbstractValueSetter
{
    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ProductManager */
    protected $productManager;

    /** @var MediaFactory */
    protected $mediaFactory;

    /**
     * @param ProductBuilderInterface $builder
     * @param ProductManager          $manager
     * @param MediaFactory            $mediaFactory
     * @param array                   $supportedTypes
     */
    public function __construct(
        ProductBuilderInterface $builder,
        ProductManager $manager,
        MediaFactory $mediaFactory,
        array $supportedTypes
    ) {
        $this->productBuilder = $builder;
        $this->productManager = $manager;
        $this->mediaFactory   = $mediaFactory;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(array $products, AttributeInterface $attribute, $data, $locale = null, $scope = null)
    {
        AttributeUtility::validateLocale($attribute, $locale);
        AttributeUtility::validateScope($attribute, $scope);

        $this->checkData($attribute, $data);

        try {
            $file = new File($data);
        } catch (FileNotFoundException $e) {
            throw InvalidArgumentException::expected(
                $attribute->getCode(),
                sprintf('a valid filename ("%s" given)', $data),
                'setter',
                'media'
            );
        }

        foreach ($products as $product) {
            $this->setMedia($attribute, $product, $file, $locale, $scope);
        }

        $this->productManager->handleAllMedia($products);
    }

    /**
     * Check if data are valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (!is_string($data)) {
            throw InvalidArgumentException::stringExpected($attribute->getCode(), 'setter', 'media');
        }
    }

    /**
     * Set media in the product value
     *
     * @param AttributeInterface $attribute
     * @param ProductInterface   $product
     * @param File               $file
     * @param string             $locale
     * @param string             $scope
     */
    protected function setMedia(AttributeInterface $attribute, ProductInterface $product, File $file, $locale, $scope)
    {
        $value = $product->getValue($attribute->getCode(), $locale, $scope);
        if (null === $value) {
            $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
        }

        if (null === $media = $value->getMedia()) {
            $media = $this->mediaFactory->createMedia($file);
        } else {
            $media->setFile($file);
        }
        $value->setMedia($media);
    }
}