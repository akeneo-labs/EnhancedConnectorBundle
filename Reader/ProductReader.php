<?php

namespace AkeneoLabs\Pim\Bundle\EnhancedConnectorBundle\Reader;

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
 * Override of the PIM product reader to add new options (delta based on date condition,
 * complete or not products, enabled or not, etc...)
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductReader extends AbstractConfigurableStepElement implements ProductReaderInterface
{
    /** @staticvar string */
    const DO_NOT_APPLY= 'doNotAppply';

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"Execution"})
     * @ChannelConstraint
     */
    protected $channel;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var MetricConverter */
    protected $metricConverter;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var boolean */
    protected $generateCompleteness;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @var string
     */
    protected $updatedCriteria;

    /** @var DateTime */
    protected $updatedFrom;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @var string
     */
    protected $enabledCriteria;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @var string
     */
    protected $completeCriteria;

    /** @var string */
    protected $jobExecutionClass;

    /** @var Cursor */
    protected $products;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ChannelManager                      $channelManager
     * @param CompletenessManager                 $completenessManager
     * @param MetricConverter                     $metricConverter
     * @param EntityManager                       $entityManager
     * @param boolean                             $generateCompleteness
     * @param string                              $jobExecutionClass
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelManager $channelManager,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        EntityManager $entityManager,
        $generateCompleteness,
        $jobExecutionClass
    ) {
        $this->pqbFactory           = $pqbFactory;
        $this->channelManager       = $channelManager;
        $this->completenessManager  = $completenessManager;
        $this->metricConverter      = $metricConverter;
        $this->entityManager        = $entityManager;
        $this->generateCompleteness = $generateCompleteness;
        $this->jobExecutionClass    = $jobExecutionClass;
    }

    /**
     * Set the channel
     *
     * @param string $channel
     *
     * @return AbstractProcessor
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get the channel
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Get updated from condition
     *
     * @return string
     */
    public function getUpdatedFrom()
    {
        return $this->updatedFrom;
    }

    /**
     * Set updated from condition
     *
     * @param string $updatedFrom updatedFrom
     *
     * @return AbstractProcessor
     */
    public function setUpdatedFrom(\DateTime $updatedFrom = null)
    {
        $this->updatedFrom = $updatedFrom;

        return $this;
    }

    /**
     * Get updated condition
     *
     * @return string
     */
    public function getUpdatedCriteria()
    {
        return $this->updatedCriteria;
    }

    /**
     * Set updated condition
     *
     * @param string $updatedCriteria
     *
     * @return AbstractProcessor
     */
    public function setUpdatedCriteria($updatedCriteria)
    {
        $this->updatedCriteria = $updatedCriteria;

        return $this;
    }

    /**
     * Get enabled condition
     *
     * @return string
     */
    public function getEnabledCriteria()
    {
        return $this->enabledCriteria;
    }

    /**
     * Set enabled condition
     *
     * @param string $enabledCriteria
     *
     * @return AbstractProcessor
     */
    public function setEnabledCriteria($enabledCriteria)
    {
        $this->enabledCriteria = $enabledCriteria;

        return $this;
    }

    /**
     * Get complete condition
     *
     * @return string
     */
    public function getCompleteCriteria()
    {
        return $this->completeCriteria;
    }

    /**
     * Set complete condition
     *
     * @param string $completeCriteria
     *
     * @return AbstractProcessor
     */
    public function setCompleteCriteria($completeCriteria)
    {
        $this->completeCriteria = $completeCriteria;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
                'channel' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => $this->channelManager->getChannelChoices(),
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_base_connector.export.channel.label',
                        'help'     => 'pim_base_connector.export.channel.help'
                    ]
                ],
                'updatedCriteria' => [
                    'type'    => 'choice',
                    'required' => true,
                    'options' => [
                        'help'    => 'akeneo_labs_pim.enhanced_product_export.updatedCriteria.help',
                        'label'   => 'akeneo_labs_pim.enhanced_product_export.updatedCriteria.label',
                        'choices'  => [
                            'fromDefinedDate'    => 'Export products updated since the defined date',
                            'fromLastExecution'  => 'Export products updated since the last execution of this job',
                            static::DO_NOT_APPLY => 'Export products regardless of their updated time',
                        ]
                    ]
                ],
                'updatedFrom' => [
                    'required' => false,
                    'options' => [
                        'help'    => 'dnd_magento_connector.export.updatedFrom.help',
                        'label'   => 'dnd_magento_connector.export.updatedFrom.label',
                    ]
                ],
                'enabledCriteria' => [
                    'type'    => 'choice',
                    'required' => true,
                    'options' => [
                        'help'    => 'akeneo_labs_pim.enhanced_product_export.enabledCriteria.help',
                        'label'   => 'akeneo_labs_pim.enhanced_product_export.enabledCriteria.label',
                        'choices'  => [
                            'onlyEnabled'        => 'Export only enabled products',
                            'onlyDisabled'       => 'Export only disabled products',
                            static::DO_NOT_APPLY => 'Export products regardless of their status',
                        ]
                    ]
                ],
                'completeCriteria' => [
                    'type'    => 'choice',
                    'required' => true,
                    'options' => [
                        'help'    => 'akeneo_labs_pim.enhanced_product_export.completeCriteria.help',
                        'label'   => 'akeneo_labs_pim.enhanced_product_export.completeCriteria.label',
                        'choices'  => [
                            'onlyComplete'       => 'Export only complete products',
                            'onlyUncomplete'     => 'Export only uncomplete products',
                            static::DO_NOT_APPLY => 'Export products regardless of their completeness',
                        ]
                    ]
                ]
            ];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->products = null;
        $channel = $this->channelManager->getChannelByCode($this->channel);

        $pqb = $this->pqbFactory->create(['default_scope' => $channel->getCode()]);

        $pqb->addFilter('categories.id', 'IN CHILDREN', [$channel->getCategory()->getId()]);

        $this->applyUpdatedFilter($pqb);
        $this->applyEnabledFilter($pqb);

        if (static::DO_NOT_APPLY !== $this->completeCriteria && $this->generateCompleteness) {
            $this->completenessManager->generateMissingForChannel($channel);
        }

        $this->applyCompleteFilter($pqb, $channel);

        $this->products = $pqb->execute();
    }

    /**
     * @{inheritdoc}
     */
    public function read()
    {
        $product = null;

        if ($this->products->valid()) {
            $product = $this->products->current();
            $this->stepExecution->incrementSummaryInfo('read');
            $this->products->next();
        }

        if (null !== $product) {
            $channel = $this->channelManager->getChannelByCode($this->channel);
            $this->metricConverter->convert($product, $channel);
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Apply updated date filter
     *
     * @param ProductQueryBuilder $pqb
     */
    protected function applyUpdatedFilter(ProductQueryBuilder $pqb)
    {
        $updatedDate = null;

        switch ($this->updatedCriteria) {
            case "fromDefinedDate":
                if (null !== $this->updatedFrom) {
                    $updatedDate = $this->updatedFrom;
                }
                break;
            case "lastExecutionDate":
                $updatedDate = $this->getLastExecutionDate();
                break;
        }

        if (null !== $updatedDate) {
            $pqb->addFilter('updated', '>=', $updatedDate);
        }
    }

    /**
     * Apply enabled filter
     *
     * @param ProductQueryBuilder $pqb
     */
    protected function applyEnabledFilter(ProductQueryBuilder $pqb)
    {
        $enabled = null;

        switch ($this->enabledCriteria) {
            case "onlyDisabled":
                $enabled = false;
                break;
            case "onlyEnabled":
                $enabled = true;
                break;
        }

        if (null !== $enabled) {
            $pqb->addFilter('enabled', '=', $enabled);
        }
    }

    /**
     * Apply complete filter
     *
     * @param ProductQueryBuilder $pqb
     * @param Channel             $channel
     */
    protected function applyCompleteFilter(ProductQueryBuilder $pqb, Channel $channel)
    {
        switch ($this->completeCriteria) {
            case "onlyComplete":
                $pqb->addFilter('completeness', '=', ['data' => 100, 'scope' => $channel->getCode()]);
                break;
            case "onlyUncomplete":
                $pqb->addFilter('completeness', '=', ['data' < 100, 'scope' => $channel->getCode()]);
                break;
        }
    }

    /**
     * Get the last execution date for the current job instance
     *
     * @return \DateTime||null
     */
    protected function getLastExecutionDate()
    {
        $query = $this->entityManager->createQuery(
            sprintf(
                "SELECT MAX(e.endTime) FROM %s e WHERE e.jobInstance = :jobInstance",
                $this->jobExecutionClass
            )
        );

        $query->setParameter('jobInstance', $this->stepExecution->getJobExecution()->getJobInstance());

        return $query->getOneOrNullResult();
    }
}
