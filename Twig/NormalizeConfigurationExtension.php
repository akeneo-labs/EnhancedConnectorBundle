<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Twig;

use Pim\Bundle\ImportExportBundle\Twig\NormalizeConfigurationExtension as NormalizeConfigurationExtensionStandard;

/**
 * Class NormalizeConfigurationExtension
 *
 * @author  Synolia
 * @package Pim\Bundle\EnhancedConnectorBundle\Twig
 */
class NormalizeConfigurationExtension extends NormalizeConfigurationExtensionStandard
{
    /**
     * @inheritdoc
     */
    public function normalizeValueFilter($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        return parent::normalizeValueFilter($value);
    }
}
