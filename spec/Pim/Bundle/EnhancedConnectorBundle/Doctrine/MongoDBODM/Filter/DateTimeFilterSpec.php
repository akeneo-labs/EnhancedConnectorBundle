<?php

namespace spec\Pim\Bundle\EnhancedConnectorBundle\Doctrine\MongoDBODM\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class DateTimeFilterSpec extends ObjectBehavior
{
    function let(AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->beConstructedWith(
            $attrValidatorHelper,
            [],
            ['updated'],
            ['>= WITH TIME','=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY']
        );
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface');
    }

    function it_only_supports_updated_field()
    {
        $this->supportsField('updated')->shouldReturn(true);
        $this->supportsField(Argument::not('updated'))->shouldReturn(false);
    }

    function it_supports_operators()
    {
        $this->supportsOperator('<')->shouldReturn(true);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('>')->shouldReturn(true);
        $this->supportsOperator('>= WITH TIME')->shouldReturn(true);
        $this->supportsOperator('BETWEEN')->shouldReturn(true);
        $this->supportsOperator('NOT BETWEEN')->shouldReturn(true);
        $this->supportsOperator('EMPTY')->shouldReturn(true);

        $this->supportsOperator('CONTAINS')->shouldReturn(false);
    }
}
