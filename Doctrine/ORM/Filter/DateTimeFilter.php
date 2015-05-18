<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

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
     * Override of the method to always get the format from DateTime.
     *
     * {@inheritdoc}
     */
    protected function getDateLiteralExpr($data, $endOfDay = false)
    {
        return $this->qb->expr()->literal($data->format('Y-m-d H:i:s'));
    }

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
}
