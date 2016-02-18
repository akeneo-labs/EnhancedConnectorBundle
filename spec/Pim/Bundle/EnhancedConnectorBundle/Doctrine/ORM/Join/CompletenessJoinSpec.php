<?php

namespace spec\Pim\Bundle\EnhancedConnectorBundle\Doctrine\ORM\Join;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class CompletenessJoinSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith($qb);
    }

    function it_is_a_completeness_join()
    {
        $this->beAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\CompletenessJoin');
    }
}
