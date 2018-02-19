<?php
namespace BatchManager\Service;

use Traversable;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use BatchManager\Entity\BatchInterface;
use BatchManager\Entity\Batch;
use BatchManager\Event\BatchEvent;
use Zend\Stdlib\ResponseInterface;
use BatchManager\Exception\EmptyBatchException;
use BatchManager\Option\BatchManagerOptions;
use BadMethodCallException;

/**
 * Batch manager
 */
class BatchManager implements BatchManagerInterface
{
    /**
     * 
     * @var \BatchManager\Option\BatchManagerOptions
     */
    protected $options;
    
    /**
     * 
     * Batch event token
     * @var BatchEvent
     */
    protected $event;
    
    /**
     * 
     * @var EventManagerInterface
     */
    protected $events;
    
    /**
     * 
     * @var BatchInterface
     */
    protected $batch;


    
    /**
     * 
     * @param \BatchManager\Option\BatchManagerOptions $options
     */
    public function __construct(BatchManagerOptions $options = null)
    {
        if (null === $options) {
            $options = new BatchManagerOptions();
        }
        $this->options = $options;
        // inspired by 
        // http://www.php.net/manual/en/function.register-shutdown-function.php#100000
        register_shutdown_function(array($this, 'batchShutdown'));
    }
    
    /**
     * Get the batch event instance
     *
     * @return BatchEvent
     */
    public function getBatchEvent()
    {
        if (!$this->event instanceof BatchEvent) {
            $this->setBatchEvent(new BatchEvent());
        }
        return $this->event;
    }
    
    /**
     * Set the batch event
     *
     * @param  BatchEvent $event
     * @return BatchManager
     */
    public function setBatchEvent(BatchEvent $event)
    {
        $this->event = $event;
        return $this;
    }
    
    /**
     * Set the event manager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return BatchManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_class($this),
        ));
        $this->events = $eventManager;
        $this->attachDefaultListeners();
        return $this;
    }
    
    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * Set the Batch
     *
     * @param BatchInterface $batch
     * @return $this
     */
    public function setBatch(BatchInterface $batch)
    {
        $this->batch = $batch;
        return $this;
    }
    
    /**
     * Retrieve the Batch
     * Lazy-loads a Batch instance if there isn't one jet.
     * 
     * @return BatchInterface;
     */
    public function getBatch()
    {
        if (null === $this->batch) {
            $this->batch = new Batch();
        }
        return $this->batch;
    }
    
    /**
     * Overload
     *
     * Proxy to batch event methods
     *
     * @param  string $method
     * @param  array $args
     * @throws \BadMethodCallException
     * @return mixed
     */
    public function __call($method, $args)
    {
        $batchEvent = $this->getBatchEvent();
        if (method_exists($batchEvent, $method)) {
            $return = call_user_func_array(array($batchEvent, $method), $args);
            return $return;
        }
    
        throw new BadMethodCallException('Method "' . $method . '" does not exist');
    }
    
    /**
     * 
     * @return \BatchManager\Option\BatchManagerOptions
     */
    public function getOptions()
    {
        return $this->options;
    }
    
    public function startBatch()
    {
        $event = $this->getBatchEvent();
        $event->setTarget($this);
        $event->setBatch($this->getBatch());
        $event->setName(BatchEvent::EVENT_BATCH_START);
        $events = $this->getEventManager();
        // Trigger start event. Registered listener will try to
        // initialize the required parameters(ID and TOKEN) on the batch 
        // to make it available to other requests.
        $events->triggerEvent($event);
        return $this;
    }
    
    
    public function processBatch()
    {
        $event = $this->getBatchEvent();
        $event->setTarget($this);
        $event->setBatch($this->getBatch());
        $event->setName(BatchEvent::EVENT_BATCH_WAKEUP);
        $events = $this->getEventManager();
        // Trigger wakeup event. The BatchLoad listener will attempt to load
        // the batch.
        $events->triggerEvent($event);
        
        // if batch could not be loaded there is nothing we can do
        $batch = $event->getBatch(); 
        if (!$batch->getBid()) {
            throw new EmptyBatchException();
        }
        
        // Define callback used to determine whether or not to short-circuit
        $shortCircuit = function ($r) use ($event) {
            if ($r instanceof ResponseInterface) {
                return true;
            }
            if ($event->getError()) {
                return true;
            }
            
            // If we are in progressive mode, break processing after x seconds.
            // BE AWARE that not all listeners are guaranteed to be run,
            // that seems to be ok since that is the whole meaning of stop propagation
            if ($event->isProgressive()) {
                /*@var $manager \BatchManager\Service\BatchManagerInterface */
                $manager = $event->getTarget();
                $stop = microtime(TRUE);
                $stopAfterXSeconds = $manager->getOptions()->getStopBatchProcessAfterXSeconds();
                $diff = round(($stop - $event->getStartTime()) * 1000, 2);
                if ($diff >= $stopAfterXSeconds) {
                    //return true;
                    $event->stopPropagation(true);
                }
            }
            
            return false;
        };
        
        // if this is the first time we are processing set the timestamp
        if (!$event->isStarted()) {
            $event->setStartTime(microtime(true));
        }
        
        
        // Trigger process event, listeners should do "their really work" here
        $event->setName(BatchEvent::EVENT_BATCH_PROCESS);
        $result = $events->triggerEventUntil($shortCircuit, $shortCircuit);
        if ($event->isError()){
            // trigger finish
            $this->finishBatch();
        }
        // if someone returns a response, return it
        $response = $result->last();
        return $response;
    }
    
    public function finishBatch()
    {
        $event = $this->getBatchEvent();
        $event->setTarget($this);
        $event->setBatch($this->getBatch());
        $event->setName(BatchEvent::EVENT_BATCH_WAKEUP);
        $events = $this->getEventManager();
        
        // Trigger wakeup event. The BatchLoad listener will attempt to load
        // the batch.
        $events->triggerEvent($event);
        
        // if batch could not be loaded there is nothing we can do
        $batch = $event->getBatch();
        if (!$batch->getBid()) {
            throw new EmptyBatchException();
        }
        
        // Define callback used to determine whether or not to short-circuit
        $shortCircuit = function ($r) use ($event) {
            if ($r instanceof ResponseInterface) {
                return true;
            }
            if ($event->getError()) {
                return true;
            }        
            return false;
        };
        
        // Trigger finished event.
        $event->setName(BatchEvent::EVENT_BATCH_FINISHED);
        $result = $events->triggerEventUntil($shortCircuit, $event);
    }
    
    public function batchShutdown()
    {
        $event = $this->getBatchEvent();
        $event->setName(BatchEvent::EVENT_BATCH_SHUTDOWN);
        // Try to keep this logic layer thin: php is shuting down...
        $this->getEventManager()->triggerEvent($event);
    }
    
    /**
     * Register the default event listeners
     *
     * @return BatchManager
     */
    protected function attachDefaultListeners()
    {
        $events = $this->getEventManager();
        // @todo register the listeners here?
    }
}
