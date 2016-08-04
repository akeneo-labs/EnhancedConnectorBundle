<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Processor;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Family to flat array processor.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyToFlatArrayProcessor implements ItemProcessorInterface
{
    /**
     * @Assert\NotBlank(groups={"Execution"})
     *
     * @var string
     */
    protected $labelLocale;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var NormalizerInterface */
    protected $transNormalizer;

    /**
     * @param NormalizerInterface       $transNormalizer
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(
        NormalizerInterface $transNormalizer,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->transNormalizer = $transNormalizer;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($family)
    {
        $flatFamily = ['code' => $family->getCode()];

        $familyLabels = $this->transNormalizer->normalize($family);
        if (!empty($familyLabels['label'])) {
            $flatFamily['label'] = $familyLabels['label'][$this->labelLocale];
        } else {
            $flatFamily['label'] = sprintf('[%s]', $family->getCode());
        }

        return $flatFamily;
    }

    /**
     * @return string
     */
    public function getLabelLocale()
    {
        return $this->labelLocale;
    }

    /**
     * @param string $labelLocale
     *
     * @return FamilyToFlatArrayProcessor
     */
    public function setLabelLocale($labelLocale)
    {
        $this->labelLocale = $labelLocale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'labelLocale' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->getActivatedLocaleChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_enhanced_connector.family_processor.locale.label',
                    'help'     => 'pim_enhanced_connector.family_processor.locale.help',
                ],
            ],
        ];
    }

    /**
     * Returns a choice list of activated locales.
     *
     * @return array
     */
    protected function getActivatedLocaleChoices()
    {
        $activatedLocaleCodes = $this->localeRepository->getActivatedLocaleCodes();

        $choices = [];
        foreach ($activatedLocaleCodes as $codes) {
            $choices[$codes] = $codes;
        }

        return $choices;
    }
}
