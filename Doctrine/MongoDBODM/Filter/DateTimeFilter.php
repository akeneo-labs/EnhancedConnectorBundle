<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;

/**
 * Override of the date filter to allow the use of the time part
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilter extends DateFilter
{
    /**
     * Override to always convert to DateTime
     *
     * {@inheritdoc}
     */
    protected function formatSingleValue($type, $value)
    {
        if (is_string($value)) {
            try {
                $value = new \DateTime($value);
            } catch (\Exception $e) {
                throw InvalidArgumentException::expected(
                    $type,
                    sprintf('a string with a correct date format (%s)', $e->getMessage()),
                    'filter',
                    'date',
                    $value
                );
            }
        } elseif (!$value instanceof \DateTime) {
            throw InvalidArgumentException::expected(
                $type,
                'array with 2 elements, string or \Datetime',
                'filter',
                'date',
                gettype($value)
            );
        }

        return $value;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param mixed  $value
     * @param string $field
     * @param string $operator
     */
    protected function applyFilter($value, $field, $operator)
    {
        switch ($operator) {
            case Operators::BETWEEN:
                $this->qb->field($field)->gte($value[0]->getTimestamp());
                $this->qb->field($field)->lte($value[1]->getTimestamp());
                break;
            case Operators::NOT_BETWEEN:
                $this->qb->addAnd(
                    $this->qb->expr()
                        ->addOr($this->qb->expr()->field($field)->lte($value[0]->getTimestamp()))
                        ->addOr($this->qb->expr()->field($field)->gte($value[1]->getTimestamp()))
                );
                break;
            case Operators::GREATER_THAN:
                $this->qb->field($field)->gt($value->getTimestamp());
                break;
            case Operators::LOWER_THAN:
                $this->qb->field($field)->lt($value->getTimestamp());
                break;
            case Operators::EQUALS:
                $this->qb->field($field)->gte($value->getTimestamp());
                $this->qb->field($field)->lte($value->getTimestamp());
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($field)->exists(false);
                break;
        }
    }
}
