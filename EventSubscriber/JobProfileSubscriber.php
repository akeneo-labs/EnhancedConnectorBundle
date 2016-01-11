<?php

namespace Pim\Bundle\EnhancedConnectorBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\ImportExportBundle\Event\JobProfileEvents;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class JobProfileSubscriber
 *
 * @author  Synolia
 * @package Pim\Bundle\EnhancedConnectorBundle\EventSubscriber
 */
class JobProfileSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            JobProfileEvents::POST_EDIT => 'onPostEdit'
        );
    }

    /**
     * Executed after job edit (just before rendering) : redirects to the custom template
     *
     * @param GenericEvent $event
     */
    public function onPostEdit(GenericEvent $event)
    {
        $jobInstance = $event->getSubject();

        if ($jobInstance->getType() == JobInstance::TYPE_EXPORT) {
            $jobInstance->getJob()->setEditTemplate('PimEnhancedConnectorBundle:JobProfile:edit.html.twig');
        }
    }
}
