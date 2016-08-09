<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Job\JobParameters\FormConfigurationProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Pim\Bundle\ImportExportBundle\JobParameters\FormConfigurationProviderInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * FormsOptions for family CSV export
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FamilyCsvExport implements FormConfigurationProviderInterface
{
    /** @var FormConfigurationProviderInterface */
    protected $simpleCsvExport;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param FormConfigurationProviderInterface $simpleCsvExport
     * @param LocaleRepositoryInterface          $localeRepository
     * @param array                              $supportedJobNames
     */
    public function __construct(
        FormConfigurationProviderInterface $simpleCsvExport,
        LocaleRepositoryInterface $localeRepository,
        array $supportedJobNames
    ) {
        $this->simpleCsvExport   = $simpleCsvExport;
        $this->localeRepository  = $localeRepository;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormConfiguration()
    {
        return array_merge([
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
        ], $this->simpleCsvExport->getFormConfiguration());
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
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
