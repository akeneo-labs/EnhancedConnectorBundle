<?php

namespace Pim\Bundle\EnhancedConnectorBundle\Controller;

use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RestController
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(LocaleRepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * Return the list of activated locales
     *
     * @return JsonResponse
     */
    public function listActivatedLocalesAction()
    {
        $activatedLocales = $this->localeRepository->getActivatedLocaleCodes();

        return new JsonResponse(array_combine($activatedLocales, $activatedLocales));
    }
}