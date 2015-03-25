<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository\ReferenceDataRepository;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferenceDataDenormalizerSpec extends ObjectBehavior
{
    function let(ConfigurationRegistryInterface $registry, RegistryInterface $doctrine)
    {
        $this->beConstructedWith(['pim_reference_data_simpleselect'], $registry, $doctrine);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\AbstractValueDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_reference_data_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_reference_data_simpleselect', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_catalog_text', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_reference_data_simpleselect', 'csv')->shouldReturn(false);
    }

    function it_returns_null_if_data_is_empty()
    {
        $this->denormalize('', 'pim_reference_data_simpleselect', 'json')->shouldReturn(null);
        $this->denormalize(null, 'pim_reference_data_simpleselect', 'json')->shouldReturn(null);
        $this->denormalize([], 'pim_reference_data_simpleselect', 'json')->shouldReturn(null);
    }

    function it_throws_an_exception_if_there_is_no_attribute_in_context()
    {
        $this->shouldThrow('Symfony\Component\Routing\Exception\InvalidParameterException')
            ->during(
                'denormalize',
                [
                    ['code' => 'battlecruiser'],
                    'pim_reference_data_simpleselect',
                    'json',
                    ['foo' => 'bar']
                ]
            );
    }

    function it_throws_an_exception_if_context_attribute_is_not_an_attribute_inteface()
    {
        $this->shouldThrow('Symfony\Component\Routing\Exception\InvalidParameterException')
            ->during(
                'denormalize',
                [
                    ['code' => 'battlecruiser'],
                    'pim_reference_data_simpleselect',
                    'json',
                    ['attribute' => 'bar']
                ]
            );
    }

    function it_denormalizes_data_into_reference_data(
        $registry,
        $doctrine,
        AttributeInterface $attribute,
        ReferenceDataInterface $battlecruiser,
        ConfigurationInterface $referenceDataConf,
        ReferenceDataRepository $referenceDataRepo
    ) {
        $attribute->getReferenceDataName()->willReturn('starship');

        $referenceDataConf->getClass()->willReturn('My\Powerfull\Starship');
        $registry->get('starship')->willReturn($referenceDataConf);

        $referenceDataRepo->findOneBy(['code' => 'battlecruiser'])->willReturn($battlecruiser);
        $doctrine->getRepository('My\Powerfull\Starship')->willReturn($referenceDataRepo);

        $this
            ->denormalize(
                ['code' => 'battlecruiser'],
                'pim_reference_data_simpleselect',
                'json',
                ['attribute' => $attribute]
            )
            ->shouldReturn($battlecruiser);
    }
}