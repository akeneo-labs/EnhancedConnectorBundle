<?php

namespace spec\Pim\Bundle\EnhancedConnectorBundle\Doctrine\ORM\Filter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class CompletenessFilterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['completeness_for_export'],
            ['<', '<=', '=', '>=', '>']
        );
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface');
    }

    function it_only_supports_completeness_field()
    {
        $this->supportsField('completeness_for_export')->shouldReturn(true);
        $this->supportsField(Argument::not('completeness_for_export'))->shouldReturn(false);
    }

    function it_supports_operators()
    {
        $this->supportsOperator('<')->shouldReturn(true);
        $this->supportsOperator('<=')->shouldReturn(true);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('>')->shouldReturn(true);
        $this->supportsOperator('>=')->shouldReturn(true);

        $this->supportsOperator('CONTAINS')->shouldReturn(false);
    }
}
