<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Doctrine\ORM\Join;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\CompletenessJoin as BaseCompletenessJoin;

/**
 * Override of the completeness join utils class in order to make
 * locale and channel optional.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessJoin extends BaseCompletenessJoin
{
    /**
     * {@inheritdoc}.
     */
    public function addJoins($completenessAlias, $locale, $scope)
    {
        $rootAlias = $this->qb->getRootAlias();
        $localeAlias = $completenessAlias.'Locale';
        $channelAlias = $completenessAlias.'Channel';

        $rootEntity = current($this->qb->getRootEntities());
        $completenessMapping = $this->qb->getEntityManager()
            ->getClassMetadata($rootEntity)
            ->getAssociationMapping('completenesses');

        $completenessClass = $completenessMapping['targetEntity'];

        $joinCondition = $completenessAlias.'.product = '.$rootAlias.'.id';

        if (null !== $locale) {
            $this->qb
                ->leftJoin(
                    'PimCatalogBundle:Locale',
                    $localeAlias,
                    'WITH',
                    $localeAlias.'.code = :cLocaleCode'
                )
                ->setParameter('cLocaleCode', $locale);

            $joinCondition .= ' AND '.$completenessAlias.'.locale = '.$localeAlias.'.id';
        }

        $this->qb
            ->leftJoin(
                'PimCatalogBundle:Channel',
                $channelAlias,
                'WITH',
                $channelAlias.'.code = :cScopeCode'
            )
            ->setParameter('cScopeCode', $scope);

        $joinCondition .= ' AND '.$completenessAlias.'.channel = '.$channelAlias.'.id';

        $this->qb->leftJoin(
            $completenessClass,
            $completenessAlias,
            'WITH',
            $joinCondition
        );

        return $this;
    }
}
