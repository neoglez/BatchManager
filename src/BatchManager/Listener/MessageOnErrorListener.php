<?php
namespace BatchManager\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use BatchManager\Event\BatchEvent;
use Zend\Session\Container as SessionContainer;
use BatchManager\Persister\BatchPersisterInterface;
use BatchManager\Entity\BatchInterface;
use BatchManager\Generator\BatchParamsGeneratorInterface;

class MessageOnErrorListener extends AbstractListenerAggregate
{

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(BatchEvent::EVENT_BATCH_FINISHED, array($this, 'messageOnError'), 1);
    }

    public function messageOnError(BatchEvent $event)
    {
        if(!$event->isError()) {
            return;
        }

        $event->setCurrentMessage("An error was detected. " . $event->getError());
        $event->setPercentage(100);
    }
}
