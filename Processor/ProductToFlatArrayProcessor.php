<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Processor;


use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor as ProductToFlatArray;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeGroupRepository;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Serializer;


class ProductToFlatArrayProcessor extends ProductToFlatArray implements ItemProcessorInterface
{
    /** @var array */
    protected $attributesToExclude;

    /** @var  array */
    protected $associationsToExclude;

    /** @var  AttributeGroupRepository */
    protected $attributeGroupRepository;

    /** @var  AssociationTypeRepository */
    protected $associationTypeRepository;

    /**
     * @param Serializer $serializer
     * @param ChannelManager $channelManager
     * @param AttributeGroupRepository $attributeGroupRepository
     * @param AssociationTypeRepository $associationTypeRepository
     * @param string[] $mediaAttributeTypes
     */
    public function __construct(
        Serializer $serializer,
        ChannelManager $channelManager,
        AttributeGroupRepository $attributeGroupRepository,
        AssociationTypeRepository $associationTypeRepository,
        array $mediaAttributeTypes
    ) {
        parent::__construct($serializer, $channelManager, $mediaAttributeTypes);

        $this->attributeGroupRepository  = $attributeGroupRepository;
        $this->associationTypeRepository = $associationTypeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $data = [
            'media'     => [],
            'product'   => []
        ];

        $mediaValues = $this->filterMediaValues($this->getMediaProductValues($product));

        foreach ($mediaValues as $mediaValue) {
            $data['media'][] = $this->serializer->normalize(
                $mediaValue->getMedia(),
                'flat',
                ['field_name' => 'media', 'prepare_copy' => true, 'value' => $mediaValue]
            );
        }

        $data['product'] = $this->filterValues($this->serializer->normalize($product, 'flat', $this->getNormalizerContext()));
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return parent::getConfigurationFields() + [
            'attributesToExclude' => [
                'type'      => 'choice',
                'options'   => [
                    'choices'   => $this->getAttributeList(),
                    'select2'   => true,
                    'multiple'  => true,
                    'label'     => 'pim_enhanced_connector.product_processor.attributes_to_exclude.label',
                    'help'      => 'pim_enhanced_connector.product_processor.attributes_to_exclude.help'
                ]
            ],
            'associationsToExclude' => [
                'type'      => 'choice',
                'options'   => [
                    'choices'   => $this->getAssociationList(),
                    'select2'   => true,
                    'multiple'  => true,
                    'label'     => 'pim_enhanced_connector.product_processor.associations_to_exclude.label',
                    'help'      => 'pim_enhanced_connector.product_processor.associations_to_exclude.help'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getAttributesToExclude()
    {
        return $this->attributesToExclude;
    }

    /**
     * @param array $attributesToExclude
     *
     * @return ProductToFlatArrayProcessor
     */
    public function setAttributesToExclude($attributesToExclude)
    {
        $this->attributesToExclude = $attributesToExclude;
        return $this;
    }

    /**
     * @return array
     */
    public function getAssociationsToExclude()
    {
        return $this->associationsToExclude;
    }

    /**
     * @param array $associationsToExclude
     *
     * @return ProductToFlatArrayProcessor
     */
    public function setAssociationsToExclude($associationsToExclude)
    {
        $this->associationsToExclude = $associationsToExclude;
        return $this;
    }

    /**
     * Gets the list of all existing attributes grouped by attribute group
     *
     * @return array
     */
    protected function getAttributeList()
    {
        $attributeGroups = $this->attributeGroupRepository->findAll();

        $results = array();
        foreach ($attributeGroups as $attributeGroup) {
            /** @var AttributeGroup $attributeGroup */
            $attributes = $attributeGroup->getAttributes();
            if (!$attributes->isEmpty()) {
                $results[$attributeGroup->getLabel()] = array();

                foreach ($attributes as $attribute) {
                    $results[$attributeGroup->getLabel()][$attribute->getCode()] = $attribute->getLabel();
                }
            }
        }
        return $results;
    }

    /**
     * Gets the list of all existing associations
     *
     * @return array
     */
    protected function getAssociationList()
    {
        $results            = array();
        $associationTypes   = $this->associationTypeRepository->findAll();

        foreach ($associationTypes as $associationType) {
            /** @var AssociationType $associationType */
            $results[$associationType->getCode()] = $associationType->getLabel();
        }

        return $results;
    }

    /**
     * Filters attributes and associations
     *
     * @param array $values
     *
     * @return array
     */
    protected function filterValues($values)
    {
        $keys       = array_keys($values);
        $toExclude  = array_merge($this->attributesToExclude, $this->associationsToExclude);

        foreach ($toExclude as $attribute) {
            /*
             * Matches localisable, scopable, price attributes (they have a suffix beginning by '-')
             * and normal attributes (which names are only the attribute code)
             */
            $foundKeys = preg_grep("/^{$attribute}(?:$|-)/s", $keys);

            foreach ($foundKeys as $foundKey) {
                unset($keys[$foundKey]);
                unset($values[$foundKey]);
            }
        }

        return $values;
    }

    /**
     * Filters the media attributes
     *
     * @param ProductValueInterface[] $mediaValues
     *
     * @return ProductValueInterface[]
     */
    protected function filterMediaValues($mediaValues)
    {
        $filteredMediaValues = array();

        foreach ($mediaValues as $mediaValue) {
            if (!in_array($mediaValue->getAttribute()->getCode(), $this->attributesToExclude)) {
                $filteredMediaValues[] = $mediaValue;
            }
        }
        return $filteredMediaValues;
    }
}