<?php

namespace spec\Pim\Bundle\EnhancedConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeToFlatArrayProcessorSpec extends ObjectBehavior
{
    function let(NormalizerInterface $transNormalizer, LocaleRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($transNormalizer, $localeRepository);
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
    }

    function it_is_a_processor()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }
}
