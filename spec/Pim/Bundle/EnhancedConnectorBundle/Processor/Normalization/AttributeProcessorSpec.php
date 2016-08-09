<?php

namespace spec\Pim\Bundle\EnhancedConnectorBundle\Processor\Normalization;

use Pim\Component\Connector\Processor\Normalization\Processor;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeProcessorSpec extends ObjectBehavior
{
    function let(Processor $baseProcessor)
    {
        $this->beConstructedWith($baseProcessor);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
    }
}
