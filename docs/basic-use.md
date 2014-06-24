# Basic use

At the most basic use you'll be doing two things:

1. Create a listener to listen to one or more events triggered by the BatchManager.
2. Attach the listener to the SharedEventManager.

We are going to use an example to explain the steps: Let's generate 500 000 users.

## Example Generate 500 000 users and save it to a csv file.

### Create a listener that listen to the BatchEvent::EVENT_BATCH_PROCESS event

    ```php
    <?php
    namespace Application\Listener;
    
    use Zend\EventManager\AbstractListenerAggregate;
    use Zend\EventManager\EventManagerInterface;
    use BatchManager\Event\BatchEvent;
    
    class GenerateUserListener extends AbstractListenerAggregate
    {
        public function attach(EventManagerInterface $events)
        {
            /*@var $sharedEvents Zend\EventManage\SharedEventManagerInterface */
            $sharedEvents = $events->getSharedManager();
            $sharedEvents->attach(
                'BatchManager\Service\BatchManager',
                BatchEvent::EVENT_BATCH_PROCESS,
                array($this, 'onBatchProcess')
            );
        }
        
        // more here
    }
    ```
