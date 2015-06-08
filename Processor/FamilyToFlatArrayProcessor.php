<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Family to flat array processor.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyToFlatArrayProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /** @var NormalizerInterface */
    protected $transNormalizer;

    /**
     * @param NormalizerInterface $transNormalizer
     */
    public function __construct(NormalizerInterface $transNormalizer)
    {
        $this->transNormalizer = $transNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($family)
    {
        $flatFamily = ['code' => $family->getCode()];

        $familyLabels = $this->transNormalizer->normalize($family);
        foreach ($familyLabels as $locale => $label) {
            $flatFamily[$locale] = $label;
        }

        return $flatFamily;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}
