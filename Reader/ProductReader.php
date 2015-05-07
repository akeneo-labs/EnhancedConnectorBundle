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
    protected $updatedCondition;

    /** @var DateTime */
    protected $updatedSince;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @var string
     */
    protected $enabledCondition;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @var string
     */
    protected $completeCondition;

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
    public function getUpdatedSince()
    {
        return $this->updatedSince;
    }

    /**
     * Set updated from condition
     *
     * @param string $updatedSince updatedSince
     *
     * @return AbstractProcessor
     */
    public function setUpdatedSince(\DateTime $updatedSince = null)
    {
        $this->updatedSince = $updatedSince;

        return $this;
    }

    /**
     * Get updated condition
     *
     * @return string
     */
    public function getUpdatedCondition()
    {
        return $this->updatedCondition;
    }

    /**
     * Set updated condition
     *
     * @param string $updatedCondition
     *
     * @return AbstractProcessor
     */
    public function setUpdatedCondition($updatedCondition)
    {
        $this->updatedCondition = $updatedCondition;

        return $this;
    }

    /**
     * Get enabled condition
     *
     * @return string
     */
    public function getEnabledCondition()
    {
        return $this->enabledCondition;
    }

    /**
     * Set enabled condition
     *
     * @param string $enabledCondition
     *
     * @return AbstractProcessor
     */
    public function setEnabledCondition($enabledCondition)
    {
        $this->enabledCondition = $enabledCondition;

        return $this;
    }

    /**
     * Get complete condition
     *
     * @return string
     */
    public function getCompleteCondition()
    {
        return $this->completeCondition;
    }

    /**
     * Set complete condition
     *
     * @param string $completeCondition
     *
     * @return AbstractProcessor
     */
    public function setCompleteCondition($completeCondition)
    {
        $this->completeCondition = $completeCondition;

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
                'updatedCondition' => [
                    'type'    => 'choice',
                    'options' => [
                        'required' => true,
                        'select2'  => true,
                        'label'   => 'pim.enhanced_connector.product_reader.updatedCondition.label',
                        'help'    => 'pim.enhanced_connector.product_reader.updatedCondition.help',
                        'choices'  => [
                            'doNotApply'        => 'pim.enhanced_connector.product_reader.updatedCondition.choices.doNotApply',
                            'fromDefinedDate'   => 'pim.enhanced_connector.product_reader.updatedCondition.choices.fromDefinedDate',
                            'fromLastExecution' => 'pim.enhanced_connector.product_reader.updatedCondition.choices.fromLastExecution'
                        ]
                    ]
                ],
                'updatedSince' => [
                    'options' => [
                        'required' => false,
                        'label' => 'pim.enhanced_connector.product_reader.updatedSince.label',
                        'help'  => 'pim.enhanced_connector.product_reader.updatedSince.help'
                    ]
                ],
                'enabledCondition' => [
                    'type'    => 'choice',
                    'options' => [
                        'required' => true,
                        'select2'  => true,
                        'help'    => 'pim.enhanced_connector.product_reader.enabledCondition.help',
                        'label'   => 'pim.enhanced_connector.product_reader.enabledCondition.label',
                        'choices'  => [
                            'onlyEnabled'  => 'pim.enhanced_connector.product_reader.enabledCondition.choices.onlyEnabled',
                            'onlyDisabled' => 'pim.enhanced_connector.product_reader.enabledCondition.choices.onlyDisabled',
                            'doNotApply'   => 'pim.enhanced_connector.product_reader.enabledCondition.choices.doNotApply',
                        ]
                    ]
                ],
                'completeCondition' => [
                    'type'    => 'choice',
                    'options' => [
                        'required' => true,
                        'select2'  => true,
                        'help'    => 'pim.enhanced_connector.product_reader.completeCondition.help',
                        'label'   => 'pim.enhanced_connector.product_reader.completeCondition.label',
                        'choices'  => [
                            'onlyComplete'   => 'pim.enhanced_connector.product_reader.completeCondition.choices.onlyComplete',
                            'onlyUncomplete' => 'pim.enhanced_connector.product_reader.completeCondition.choices.onlyUncomplete',
                            'doNotApply'     => 'pim.enhanced_connector.product_reader.completeCondition.choices.doNotApply'
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

        if ('doNotApply' !== $this->completeCondition && $this->generateCompleteness) {
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

        switch ($this->updatedCondition) {
            case "fromDefinedDate":
                if (null !== $this->updatedSince) {
                    $updatedDate = $this->updatedSince;
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

        switch ($this->enabledCondition) {
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
        switch ($this->completeCondition) {
            case "onlyComplete":
                $pqb->addFilter('completeness', '=', 100, ['scope' => $channel->getCode()]);
                break;
            case "onlyUncomplete":
                $pqb->addFilter('completeness', '<', 100, ['scope' => $channel->getCode()]);
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
