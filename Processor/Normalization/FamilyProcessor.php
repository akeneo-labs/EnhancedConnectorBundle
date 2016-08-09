<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Processor\Normalization;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Family processor.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var NormalizerInterface */
    protected $transNormalizer;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param NormalizerInterface $transNormalizer
     */
    public function __construct(NormalizerInterface $transNormalizer)
    {
        $this->transNormalizer = $transNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($family)
    {
        $parameters = $this->stepExecution->getJobParameters();
        $labelLocale = $parameters->get('labelLocale');

        $flatFamily = ['code' => $family->getCode()];

        $familyLabels = $this->transNormalizer->normalize($family);
        if (!empty($familyLabels['labels'])) {
            $flatFamily['label'] = $familyLabels['labels'][$labelLocale];
        } else {
            $flatFamily['label'] = sprintf('[%s]', $family->getCode());
        }

        return $flatFamily;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
