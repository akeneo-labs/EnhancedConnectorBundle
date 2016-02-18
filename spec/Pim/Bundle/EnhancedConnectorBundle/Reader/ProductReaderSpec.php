<?php

namespace spec\Pim\Bundle\EnhancedConnectorBundle\Reader;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\TransformBundle\Converter\MetricConverter;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductReaderSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelManager $channelManager,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        EntityManager $entityManager
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $channelManager,
            $completenessManager,
            $metricConverter,
            $entityManager,
            true,
            'Akeneo\Component\Batch\Model\JobExecution'
        );
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
    }

    function it_is_a_product_reader()
    {
        $this->shouldImplement('Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemReaderInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }
}
