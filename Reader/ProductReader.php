<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Reader;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Job\ExitStatus;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel as ChannelConstraint;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\TransformBundle\Converter\MetricConverter;
use Pim\Component\Catalog\Model\ChannelInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Override of the PIM product reader to add new options (delta based on date condition,
 * complete or not products, enabled or not, etc...).
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductReader extends AbstractConfigurableStepElement implements ProductReaderInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var MetricConverter */
    protected $metricConverter;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** 
     * @var ObjectDetacherInterface
     * @deprecated Will be removed in 1.3 
     */
    protected $objectDetacher;

    /** @var bool */
    protected $generateCompleteness;

    /** @var string */
    protected $jobExecutionClass;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var CursorInterface */
    protected $products;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @ChannelConstraint
     *
     * @var string
     */
    protected $channel;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     *
     * @var string
     */
    protected $updatedCondition;

    /**
     * @Assert\DateTime(groups={"Execution"})
     *
     * @var string
     */
    protected $updatedSince;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     *
     * @var string
     */
    protected $enabledCondition;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     *
     * @var string
     */
    protected $categorizationCondition;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     *
     * @var string
     */
    protected $completeCondition;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ChannelManager                      $channelManager
     * @param CompletenessManager                 $completenessManager
     * @param MetricConverter                     $metricConverter
     * @param EntityManagerInterface              $entityManager
     * @param ObjectDetacherInterface             $objectDetacher
     * @param bool                                $generateCompleteness
     * @param string                              $jobExecutionClass
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelManager $channelManager,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        EntityManagerInterface $entityManager,
        ObjectDetacherInterface $objectDetacher,
        $generateCompleteness,
        $jobExecutionClass
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->channelManager = $channelManager;
        $this->completenessManager = $completenessManager;
        $this->metricConverter = $metricConverter;
        $this->entityManager = $entityManager;
        $this->objectDetacher = $objectDetacher;
        $this->generateCompleteness = $generateCompleteness;
        $this->jobExecutionClass = $jobExecutionClass;
    }

    /**
     * Sets the channel code.
     *
     * @param string $channel
     *
     * @return ProductReader
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Gets the channel code.
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Gets updated from condition.
     *
     * @return string
     */
    public function getUpdatedSince()
    {
        return $this->updatedSince;
    }

    /**
     * Sets updated from condition.
     *
     * @param string $updatedSince
     *
     * @return ProductReader
     */
    public function setUpdatedSince($updatedSince = null)
    {
        $this->updatedSince = $updatedSince;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedCondition()
    {
        return $this->updatedCondition;
    }

    /**
     * @param string $updatedCondition
     *
     * @return ProductReader
     */
    public function setUpdatedCondition($updatedCondition)
    {
        $this->updatedCondition = $updatedCondition;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategorizationCondition()
    {
        return $this->categorizationCondition;
    }

    /**
     * @param string $categorizationCondition
     *
     * @return ProductReader
     */
    public function setCategorizationCondition($categorizationCondition)
    {
        $this->categorizationCondition = $categorizationCondition;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnabledCondition()
    {
        return $this->enabledCondition;
    }

    /**
     * @param string $enabledCondition
     *
     * @return ProductReader
     */
    public function setEnabledCondition($enabledCondition)
    {
        $this->enabledCondition = $enabledCondition;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompleteCondition()
    {
        return $this->completeCondition;
    }

    /**
     * @param string $completeCondition
     *
     * @return ProductReader
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
            'channel'                 => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help',
                ],
            ],
            'updatedCondition'        => [
                'type'    => 'choice',
                'options' => [
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_enhanced_connector.product_reader.updatedCondition.label',
                    'help'     => 'pim_enhanced_connector.product_reader.updatedCondition.help',
                    'choices'  => [
                        'doNotApply'        => 'pim_enhanced_connector.product_reader.updatedCondition.choices.doNotApply',
                        'fromDefinedDate'   =>
                            'pim_enhanced_connector.product_reader.updatedCondition.choices.fromDefinedDate',
                        'fromLastExecution' =>
                            'pim_enhanced_connector.product_reader.updatedCondition.choices.fromLastExecution',
                    ],
                ],
            ],
            'updatedSince'            => [
                'options' => [
                    'required' => false,
                    'label'    => 'pim_enhanced_connector.product_reader.updatedSince.label',
                    'help'     => 'pim_enhanced_connector.product_reader.updatedSince.help',
                ],
            ],
            'enabledCondition'        => [
                'type'    => 'choice',
                'options' => [
                    'required' => true,
                    'select2'  => true,
                    'help'     => 'pim_enhanced_connector.product_reader.enabledCondition.help',
                    'label'    => 'pim_enhanced_connector.product_reader.enabledCondition.label',
                    'choices'  => [
                        'onlyEnabled'  => 'pim_enhanced_connector.product_reader.enabledCondition.choices.onlyEnabled',
                        'onlyDisabled' => 'pim_enhanced_connector.product_reader.enabledCondition.choices.onlyDisabled',
                        'doNotApply'   => 'pim_enhanced_connector.product_reader.enabledCondition.choices.doNotApply',
                    ],
                ],
            ],
            'categorizationCondition' => [
                'type'    => 'choice',
                'options' => [
                    'required' => true,
                    'select2'  => true,
                    'help'     => 'pim_enhanced_connector.product_reader.categorizationCondition.help',
                    'label'    => 'pim_enhanced_connector.product_reader.categorizationCondition.label',
                    'choices'  => [
                        'onlyCategorized'    =>
                            'pim_enhanced_connector.product_reader.categorizationCondition.choices.onlyCategorized',
                        'onlyNonCategorized' =>
                            'pim_enhanced_connector.product_reader.categorizationCondition.choices.onlyNonCategorized',
                        'doNotApply'         =>
                            'pim_enhanced_connector.product_reader.categorizationCondition.choices.doNotApply',
                    ],
                ],
            ],
            'completeCondition'       => [
                'type'    => 'choice',
                'options' => [
                    'required' => true,
                    'select2'  => true,
                    'help'     => 'pim_enhanced_connector.product_reader.completeCondition.help',
                    'label'    => 'pim_enhanced_connector.product_reader.completeCondition.label',
                    'choices'  => [
                        'onlyComplete'   =>
                            'pim_enhanced_connector.product_reader.completeCondition.choices.onlyComplete',
                        'onlyUncomplete' =>
                            'pim_enhanced_connector.product_reader.completeCondition.choices.onlyUncomplete',
                        'doNotApply'     => 'pim_enhanced_connector.product_reader.completeCondition.choices.doNotApply',
                    ],
                ],
            ],
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

        $this->applyCategorizationFilter($pqb, $channel);
        $this->applyUpdatedFilter($pqb);
        $this->applyEnabledFilter($pqb);

        if ('doNotApply' !== $this->completeCondition && $this->generateCompleteness) {
            $this->completenessManager->generateMissingForChannel($channel);
        }

        $this->applyCompleteFilter($pqb, $channel);

        $this->products = $pqb->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $product = null;

        if ($this->products->valid()) {
            $product = $this->products->current();
            $this->stepExecution->incrementSummaryInfo('read');
            $this->products->next();
        }

        if (null === $product || false === $product) {
            return null;
        }
        
        $channel = $this->channelManager->getChannelByCode($this->channel);
        $this->metricConverter->convert($product, $channel);

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
     * Applies updated date filter.
     *
     * @param ProductQueryBuilderInterface $pqb
     */
    protected function applyUpdatedFilter(ProductQueryBuilderInterface $pqb)
    {
        $updatedDate = null;

        switch ($this->updatedCondition) {
            case 'fromDefinedDate':
                if (null !== $this->updatedSince) {
                    $updatedDate = $this->updatedSince;
                }
                break;
            case 'fromLastExecution':
                $updatedDate = $this->getLastExecutionDate();
                break;
        }

        if (null !== $updatedDate) {
            $pqb->addFilter('updated', '>= WITH TIME', $updatedDate);
        }
    }

    /**
     * Applies enabled filter.
     *
     * @param ProductQueryBuilderInterface $pqb
     */
    protected function applyEnabledFilter(ProductQueryBuilderInterface $pqb)
    {
        $enabled = null;

        switch ($this->enabledCondition) {
            case 'onlyDisabled':
                $enabled = false;
                break;
            case 'onlyEnabled':
                $enabled = true;
                break;
        }

        if (null !== $enabled) {
            $pqb->addFilter('enabled', '=', $enabled);
        }
    }

    /**
     * Applies complete filter.
     *
     * @param ProductQueryBuilderInterface $pqb
     * @param ChannelInterface             $channel
     */
    protected function applyCompleteFilter(ProductQueryBuilderInterface $pqb, ChannelInterface $channel)
    {
        switch ($this->completeCondition) {
            case 'onlyComplete':
                $pqb->addFilter('completeness_for_export', '=', 100, ['scope' => $channel->getCode()]);
                break;
            case 'onlyUncomplete':
                $pqb->addFilter('completeness_for_export', '<', 100, ['scope' => $channel->getCode()]);
                break;
        }
    }

    /**
     * Applies categorization filter.
     *
     * @param ProductQueryBuilderInterface $pqb
     * @param ChannelInterface             $channel
     */
    protected function applyCategorizationFilter(ProductQueryBuilderInterface $pqb, ChannelInterface $channel)
    {
        switch ($this->categorizationCondition) {
            case 'onlyCategorized':
                $pqb->addFilter('categories.id', 'IN CHILDREN', [$channel->getCategory()->getId()]);
                break;
            case 'onlyNonCategorized':
                $pqb->addFilter('categories.id', 'UNCLASSIFIED', []);
                break;
        }
    }

    /**
     * Gets the last successful execution date for the current job instance.
     *
     * @return \DateTime||null
     */
    protected function getLastExecutionDate()
    {
        $query = $this->entityManager->createQuery(
            sprintf(
                'SELECT MAX(e.startTime) FROM %s e WHERE e.jobInstance = :jobInstance AND e.exitCode = :completed',
                $this->jobExecutionClass
            )
        );

        $query->setParameter('jobInstance', $this->stepExecution->getJobExecution()->getJobInstance());
        $query->setParameter('completed', ExitStatus::COMPLETED);

        $utcDateTime = $query->getOneOrNullResult();

        if (is_array($utcDateTime)) {
            $utcTimeZone = new \DateTimeZone('Etc/UTC');
            $utcDateTime = new \DateTime(reset($utcDateTime), $utcTimeZone);
        }

        $dateTime = new \DateTime();

        $dateTime->setTimestamp($utcDateTime->getTimestamp());

        return $dateTime;
    }
}
