<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel as ChannelConstraint;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilder;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\TransformBundle\Converter\MetricConverter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Override of the PIM product reader to add the capability to send
 * CSV file and images to SFTP targets
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SftpCsvProductWriter extends AbstractConfigurableStepElement implements ProductReaderInterface
{
}
