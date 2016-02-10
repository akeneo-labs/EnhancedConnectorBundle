<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\CompletenessFilter as BaseCompletenessFilter;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

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
    /** @var ChannelManager */
    protected $channelManager;

    /**
     * Instanciate the filter
     *
     * @param array $supportedFields
     * @param array $supportedOperators
     * @param ChannelManager $channelManager
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = [],
        ChannelManager $channelManager
    ) {
        parent::__construct($supportedFields, $supportedOperators);
        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        $this->checkValue($field, $value, $locale, $scope);

        $channel = $this->channelManager->getChannelByCode($scope);

        foreach ($channel->getLocales() as $locale) {
            $field = sprintf(
                "%s.%s.%s-%s",
                ProductQueryUtility::NORMALIZED_FIELD,
                'completenesses',
                $scope,
                $locale
            );
            $value = intval($value);

            $this->qb->addOr(
                $this->getExpr($value, $field, $operator)
            );
        }

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

    /**
     * Generate an Expr for query with the given operator on the value
     *
     * @param integer $value
     * @param string  $field
     * @param string  $operator
     *
     * @return Doctrine\ODM\MongoDB\Query\Expr
     */
    protected function getExpr($value, $field, $operator)
    {
        $expr = $this->qb->expr();

        switch ($operator) {
            case Operators::EQUALS:
                $expr->field($field)->equals($value);
                break;
            case Operators::LOWER_THAN:
                $expr->field($field)->lt($value);
                break;
            case Operators::GREATER_THAN:
                $expr->field($field)->gt($value);
                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $expr->field($field)->lte($value);
                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $expr->field($field)->gte($value);
                break;
        }

        return $expr;
    }
}
