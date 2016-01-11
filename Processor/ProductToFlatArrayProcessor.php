<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Processor;


use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor as ProductToFlatArray;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeGroupRepository;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Class ProductToFlatArrayProcessor
 *
 * @author  Synolia
 * @package Pim\Bundle\EnhancedConnectorBundle\Processor
 */
class ProductToFlatArrayProcessor extends ProductToFlatArray implements ItemProcessorInterface
{
    /** @var array */
    protected $attributesToExclude;

    /** @var  array */
    protected $associationTypesToExclude;

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
        $data = parent::process($product);

        $data['product'] = $this->filterValues($data['product']);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMediaProductValues(ProductInterface $product)
    {
        $values = parent::getMediaProductValues($product);

        return $this->filterMediaValues($values);
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
            'associationTypesToExclude' => [
                'type'      => 'choice',
                'options'   => [
                    'choices'   => $this->getAssociationTypeList(),
                    'select2'   => true,
                    'multiple'  => true,
                    'label'     => 'pim_enhanced_connector.product_processor.association_types_to_exclude.label',
                    'help'      => 'pim_enhanced_connector.product_processor.association_types_to_exclude.help'
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
    public function getAssociationTypesToExclude()
    {
        return $this->associationTypesToExclude;
    }

    /**
     * @param array $associationTypesToExclude
     *
     * @return ProductToFlatArrayProcessor
     */
    public function setAssociationTypesToExclude($associationTypesToExclude)
    {
        $this->associationTypesToExclude = $associationTypesToExclude;
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
     * Gets the list of all existing association types
     *
     * @return array
     */
    protected function getAssociationTypeList()
    {
        $results            = array();
        $associationTypes   = $this->associationTypeRepository->findAll();

        foreach ($associationTypes as $associationType) {
            $results[$associationType->getCode()] = $associationType->getLabel();
        }

        return $results;
    }

    /**
     * Filters attributes and association types
     *
     * @param array $values
     *
     * @return array
     */
    protected function filterValues($values)
    {
        $keys       = array_keys($values);
        $toExclude  = array_merge($this->attributesToExclude, $this->associationTypesToExclude);

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
