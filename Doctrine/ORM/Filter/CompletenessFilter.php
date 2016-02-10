<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Doctrine\ORM\Filter;

use Pim\Bundle\EnhancedConnectorBundle\Doctrine\ORM\Join\CompletenessJoin;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\CompletenessFilter as BaseCompletenessFilter;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;

/**
 * Override of the completeness filter that allows to apply completeness
 * filter only on scope (channel) without locale restriction
 *
 * @author    Benoit Jacquemont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends BaseCompletenessFilter
{
    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        $this->checkValue($field, $value, $locale, $scope);

        $joinAlias = $this->getUniqueAlias('filterCompleteness');
        $field = $joinAlias . '.ratio';

        $util = new CompletenessJoin($this->qb);
        $util->addJoins($joinAlias, $locale, $scope);

        $this->qb->andWhere($this->prepareCriteriaCondition($field, $operator, $value));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkValue($field, $value, $locale, $scope)
    {
        if (!is_numeric($value)) {
            throw InvalidArgumentException::numericExpected($field, 'filter', 'completeness', gettype($value));
        }

        if (null === $scope) {
            throw new InvalidArgumentException('Scope expected for completeness filter. None given.');
        }
    }
}
