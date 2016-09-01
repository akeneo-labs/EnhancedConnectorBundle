<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Processor\Normalization;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Attribute processor.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeProcessor implements ItemProcessorInterface
{
    /** @const string */
    const ITEM_SEPARATOR = ',';

    /** @var ItemProcessorInterface */
    protected $baseProcessor;

    /**
     * @param ItemProcessorInterface $baseProcessor
     */
    public function __construct(ItemProcessorInterface $baseProcessor)
    {
        $this->baseProcessor = $baseProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function process($attribute)
    {
        $normalizedAttribute = $this->baseProcessor->process($attribute);
        $normalizedAttribute['families'] = $this->getAttributeFamilyCodes($attribute);

        return $normalizedAttribute;
    }

    /**
     * Returns the list of all the family codes of the attribute.
     *
     * @param AttributeInterface $attribute
     *
     * @return string
     */
    protected function getAttributeFamilyCodes(AttributeInterface $attribute)
    {
        $families = $attribute->getFamilies();

        $familyCodes = [];
        if (null !== $families) {
            foreach ($families as $family) {
                $familyCodes[] = $family->getCode();
            }
        }

        return implode(self::ITEM_SEPARATOR, $familyCodes);
    }
}
