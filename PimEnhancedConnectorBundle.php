<?php

namespace Pim\Bundle\EnhancedConnectorBundle;

use Pim\Bundle\EnhancedConnectorBundle\DependencyInjection\Compiler\RegisterFormExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Enhanced connector bundle.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimEnhancedConnectorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        if (class_exists('PimEnterprise\Bundle\WorkflowBundle\PimEnterpriseWorkflowBundle')) {
            $container->addCompilerPass(new RegisterFormExtensionPass());
        }
    }
}
