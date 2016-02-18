<?php

namespace spec\Pim\Bundle\EnhancedConnectorBundle\Processor;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductToFlatArrayProcessorSpec extends ObjectBehavior
{
    function let(
        Serializer $serializer,
        ChannelManager $channelManager,
        ProductBuilderInterface $productBuilder,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->beConstructedWith(
            $serializer,
            $channelManager,
            $productBuilder,
            ['pim_catalog_file', 'pim_catalog_image'],
            ['.'],
            ['yyyy-mm-dd'],
            $objectDetacher
        );
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
