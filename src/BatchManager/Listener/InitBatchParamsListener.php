<?php
namespace BatchManager\Listener;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use BatchManager\Event\BatchEvent;
use Zend\Session\Container as SessionContainer;
use BatchManager\Persister\BatchPersisterInterface;
use BatchManager\Entity\BatchInterface;
use BatchManager\Generator\BatchParamsGeneratorInterface;

class InitBatchParamsListener extends AbstractListenerAggregate
{
    
    /**
     * 
     * @var $batchMapper BatchPersisterInterface;
     */
    protected $batchMapper;
    
    /**
     * Generator that assign a value to the batch bid and the token
     * 
     * @var \BatchManager\Generator\AbstractBatchParamsGenerator
     */
    protected $paramsGenerator;
    
    /**
     * 
     * @param BatchPersisterInterface $mapper
     * @return InitBatchParamsListener
     */
    public function __construct(
        BatchPersisterInterface $mapper,
        BatchParamsGeneratorInterface $paramsGenerator
    ) {
        $this->batchMapper = $mapper;
        $this->paramsGenerator = $paramsGenerator;
    }
    
    /**
     * 
     * @param BatchParamsGeneratorInterface $paramsGenerator
     * @return \BatchManager\Listener\InitBatchParamsListener
     */
    public function setParamsGenerator(BatchParamsGeneratorInterface $paramsGenerator)
    {
        $this->paramsGenerator = $paramsGenerator;
        return $this;
    }
    
    /**
     * 
     * @return \BatchManager\Generator\AbstractBatchParamsGenerator
     */
    public function getParamsGenerator()
    {
        return $this->paramsGenerator;
    }
    
    /**
     * 
     * @param  $bm BatchPersisterInterface
     * @return \BatchManager\Listener\InitBatchParamsListener
     */
    public function setBatchMapper($bm)
    {
        $this->batchMapper = $bm;
        return $this;
    }
    
    /**
     * 
     * @return BatchPersisterInterface the $batchMapper
     */
    public function getBatchMapper()
    {
        return $this->batchMapper;
    }
    
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
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(BatchEvent::EVENT_BATCH_START, array($this, 'onBatchStart'), -1000);
        $this->listeners[] = $events->attach(BatchEvent::EVENT_BATCH_WAKEUP, array($this, 'onBatchWakeUp'), -1000);
    }
    
    public function onBatchStart(BatchEvent $event)
    {
        // we are going to init here the id
        // if there is one already we do nothing
        $batch = $event->getBatch();
        
        // first the ID, otherwise we won't be able to start
        if (!$batch->getBid()) {
            $this->initBatchId($batch);
            if (!$batch->getBid()) {
                throw new \Exception("Batch ID could not be generated");
            }
        }
        
        // second the token
        if (!$batch->getToken()) {
            $this->initBatchToken($batch);
            if (!$batch->getToken()) {
                throw new \Exception("Batch token could not be generated");
            }
        }
        
        $batch->setTimestamp(time());
        
        $this->getBatchMapper()->persistBatch($batch);
        
        // At this point we mark the flag in event to let know others
        // that batch is loaded
        $event->isBatchLoaded(true);
    }
    
    public function onBatchWakeUp(BatchEvent $event)
    {
        $batch = $event->getBatch();
        // if the batch is loaded we dont't do anything
        if ($event->isBatchLoaded()) {
            return;
        } elseif (!$batch->getBid()) {
            throw new \Exception("Can't wake up batch without batch ID");
        } else {
            // we loaded it with the mapper
            $batchId = $batch->getBid();
            // generate the token based on the id
            $token = $this->paramsGenerator->generateToken($batchId);
            $batch->setToken($token);
            $awakedBatch = $this->getBatchMapper()->retreiveBatch($batchId, $token);
            $event->setBatch($awakedBatch);
            // mark as loaded
            $event->isBatchLoaded(true);
        }
        
    }
    
    /**
     * Assign ID to the batch
     * 
     * @param BatchInterface $batch
     * @return \BatchManager\Listener\InitBatchParamsListener
     */
    public function initBatchId(BatchInterface $batch)
    {
        $bid = $this->paramsGenerator->generateBatchId();
        $batch->setBid($bid);
        return $this;
    }
    
    /**
     * Assign a HMAC token to the the batch
     * 
     * @param BatchInterface $batch
     * @return \BatchManager\Listener\InitBatchParamsListener
     */
    public function initBatchToken(BatchInterface $batch)
    {
        $token = $this->paramsGenerator->generateToken();
        $batch->setToken($token);
        return $this;
    }
}
