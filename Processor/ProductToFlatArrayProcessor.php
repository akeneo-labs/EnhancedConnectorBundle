<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Processor;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\BaseConnectorBundle\Processor\ProductToFlatArrayProcessor as BaseProductToFlatArrayProcessor;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Symfony\Component\Serializer\Serializer;

/**
 * Overrides original product processor to detach product once processed.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToFlatArrayProcessor extends BaseProductToFlatArrayProcessor
{
    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /**
     * @param Serializer                   $serializer
     * @param ChannelManager               $channelManager
     * @param string                       $uploadDirectory
     * @param ObjectDetacherInterface|null $objectDetacher
     */
    public function __construct(
        Serializer $serializer,
        ChannelManager $channelManager,
        $uploadDirectory,
        ObjectDetacherInterface $objectDetacher = null
    ) {
        parent::__construct($serializer, $channelManager, $uploadDirectory);

        $this->objectDetacher = $objectDetacher;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $normalizedProduct = parent::process($product);

        $this->objectDetacher->detach($product);

        return $normalizedProduct;
    }
}
